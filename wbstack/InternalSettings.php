<?php

// TODO if possible don't show these on api help page, even when only shown in the backend
// TODO added inter service communication auth

$wgAPIModules['wbstackInit'] = WbStackApiInit::class;
$wgAPIModules['wbstackUpdate'] = WbStackApiUpdate::class; // Runs update.php for the wiki
$wgAPIModules['wbstackPlatformOauthGet'] = WbStackPlatformOauthGet::class;

class WbStackPlatformReservedUser{
    const PLATFORM_RESERVED_USER = 'PlatformReservedUser';
    const PLATFORM_RESERVED_EMAIL = 'PlatformReservedUser.mediawikiuser.platform@wbstack.com';

    public static function getUser() {
        return \User::newFromName( self::PLATFORM_RESERVED_USER );
    }

    public static function createIfNotExists() {
        $user = self::getUser();

        // Check the user doesnt already exist
        // TODO the user could be renamed, so check if # of users > 0 instead here..
        if($user->idForName() !== 0){
            // null as no action needed
            return null;
        }

        $user->setEmail( self::PLATFORM_RESERVED_EMAIL );

        // Create the user
        // TODO check create status?
        $createStatus = MediaWiki\Auth\AuthManager::singleton()->autoCreateUser(
            $user,
            MediaWiki\Auth\AuthManager::AUTOCREATE_SOURCE_MAINT,
            false
        );

        // Mark the e-mail address confirmed.
        $user->confirmEmail();
        $user->saveSettings();

        // Promote the user to platform user??
        array_map( [ $user, 'addGroup' ], [ 'platform' ] );

        return true;
    }

    public static function createOauthConsumer($consumerName, $version, $grants, $callbackUrl) {
        // ### Setup oauth consumer...
        // LOGIC mainly from https://github.com/wikimedia/mediawiki-extensions-OAuth/blob/master/maintenance/createOAuthConsumer.php ?
        // EXECUTION of script from https://github.com/wmde/wikibase-docker/blob/master/wikibase/1.33/bundle/extra-install.sh#L7 ?

        // callbackUrl in docker: $QS_PUBLIC_SCHEME_HOST_AND_PORT/api.php
        $data = [
            'action' => 'propose',
            'name'         => $consumerName,
            'version'      => $version,
            'description'  => $consumerName,
            'callbackUrl'  => $callbackUrl,
            'callbackIsPrefix' => true,
            'grants' => '["' . implode( '","', $grants) . '"]',
            'granttype' => 'normal',
            'ownerOnly' => false,
            'email' => WbStackPlatformReservedUser::PLATFORM_RESERVED_EMAIL,
            'wiki' => '*',
            'rsaKey' => '',
            'agreement' => true,
            'restrictions' => \MWRestrictions::newDefault(),
        ];

        $context = \RequestContext::getMain();
        $context->setUser( self::getUser() );

        $dbw = \MediaWiki\Extensions\OAuth\MWOAuthUtils::getCentralDB( DB_MASTER );
        $control = new \MediaWiki\Extensions\OAuth\MWOAuthConsumerSubmitControl( $context, $data, $dbw );
        $status = $control->submit();

        if ( !$status->isGood() ) {
            // TODO return more info...
            return false;
        }

        $cmr = $status->value['result']['consumer'];

        $data = [
            'action' => 'approve',
            'consumerKey'  => $cmr->getConsumerKey(),
            'reason'       => 'Approved by platform',
            'changeToken'  => $cmr->getChangeToken( $context ),
        ];
        $control = new \MediaWiki\Extensions\OAuth\MWOAuthConsumerSubmitControl( $context, $data, $dbw );
        $approveStatus = $control->submit();

        if ( !$approveStatus->isGood() ) {
            // TODO return more info...
            return false;
        }

        return true;
    }

    public static function getOAuthConsumer($consumerName, $version) {
        $user = self::getUser();
        // TODO create the oauth consumer on the fly if it doesnt exist (needs grants and callbackurl)

        // Bail if the user not registered..
        if($user->idForName() === 0){
            return false;
        }

        $db = \MediaWiki\Extensions\OAuth\MWOAuthUtils::getCentralDB( DB_REPLICA );

        // $c is a MWOAuthConsumer
        // https://github.com/wikimedia/mediawiki-extensions-OAuth/blob/master/includes/backend/MWOAuthConsumer.php
        $c = \MediaWiki\Extensions\OAuth\MWOAuthConsumer::newFromNameVersionUser(
            $db,
            $consumerName,
            $version,
            $user->getId()
        );

        if( $c === false ) {
            return false;
        }

        return [
            'agent' => $c->getName(),
            'consumerKey' => $c->getConsumerKey(),
            'consumerSecret' => \MediaWiki\Extensions\OAuth\MWOAuthUtils::hmacDBSecret( $c->getSecretKey() ),
        ];
    }
}

class WbStackApiInit extends ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        $this->executeWikiUser();
        WbStackPlatformReservedUser::createIfNotExists();
    }
    public function executeWikiUser() {
        // This code is a combination of maintenance/createAndPromote.php and SpecialPasswordReset.php ideas

        $username = $this->getParameter('username');
        $email = $this->getParameter('email');

        // TODO validation? but our app should always send the right stuff now anyway..
        // TODO call back to the API and make sure that this email address is a manager of the wiki?

        // Get a user object
        $user = User::newFromName( $username );

        // Check the user doesnt already exist (they shouldn't)
        // TODO the user could be renamed, so check if # of users > 0 instead here..
        // ie. if there is any user registered..
        if($user->idForName() !== 0){
            $res = [
                'success' => '0',
            ];
            $this->getResult()->addValue( null, $this->getModuleName(), $res );
            return;
        }

        $user->setEmail( $email );

        // Create the user
        $createStatus = MediaWiki\Auth\AuthManager::singleton()->autoCreateUser(
            $user,
            MediaWiki\Auth\AuthManager::AUTOCREATE_SOURCE_MAINT,
            false
        );

        // Mark the e-mail address confirmed.
        $user->confirmEmail();
        $user->saveSettings();

        // Promote the user (admin and crat)
        $promotions = [
            'sysop',
            'bureaucrat',
            //'interface-admin',
            //'bot'
        ];
        array_map( [ $user, 'addGroup' ], $promotions );

        // Reset the password (triggers email)
        $services = MediaWiki\MediaWikiServices::getInstance();
        $passwordReset = $services->getPasswordReset();
        $resetStatus = $passwordReset->execute( $user, $username, $email );

        // Update the site stats
        $ssu = SiteStatsUpdate::factory( [ 'users' => 1 ] );
        $ssu->doUpdate();

        $res = [
            'success' => '1',
            'userId' => $user->getId(),
        ];
        $this->getResult()->addValue( null, $this->getModuleName(), $res );
    }
    public function getAllowedParams() {
        return [
            'username' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ],
            'email' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ],
        ];
    }
}

class WbStackApiUpdate extends ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        global $wikWiki;
        
        @set_time_limit( 60*5 ); // 5 mins maybe D:
		@ini_set( 'memory_limit', '-1' ); // also try to disable the memory limit? Is this even a good idea?

		// Run update.php
		$cmd = 'WW_DOMAIN=' . $wikWiki->requestDomain . ' php ' . __DIR__ . '/maintenance/update.php --quick';
		exec($cmd, $out, $return);

		// Return appropriate result
		$res = [
			'script' => 'maintenance/update.php',
			'return' => $return,
			'output' => $out,
		];
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
    }
    public function getAllowedParams() {
        return [];
    }
}

class WbStackPlatformOauthGet extends ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        // Try and get the required consumer
        $consumerData = WbStackPlatformReservedUser::getOAuthConsumer(
            $this->getParameter('consumerName'),
            $this->getParameter('consumerVersion')
        );

        // If it doesnt exist, make sure the user and consumer do
        if(!$consumerData) {
            $callbackUrl = 'https://' . $GLOBALS[WIKWIKI_GLOBAL]->requestDomain . $this->getParameter('callbackUrlTail');

            WbStackPlatformReservedUser::createIfNotExists();
            WbStackPlatformReservedUser::createOauthConsumer(
                $this->getParameter('consumerName'),
                $this->getParameter('consumerVersion'),
                $this->getParameter('grants'),
                $callbackUrl
            );
            $consumerData = WbStackPlatformReservedUser::getOAuthConsumer(
                $this->getParameter('consumerName'),
                $this->getParameter('consumerVersion')
            );
        }

        // Return appropriate result
        if(!$consumerData) {
            $res = ['success' => 0];
        } else {
            $res = [
                'success' => '1',
                'data' => $consumerData,
            ];
        }

        $this->getResult()->addValue( null, $this->getModuleName(), $res );

    }
    public function getAllowedParams() {
        return [
            'consumerName' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ],
            'consumerVersion' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ],
            'grants' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_ISMULTI => true,
            ],
            'callbackUrlTail' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ],
        ];
    }
}
