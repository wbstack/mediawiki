<?php

namespace WBStack\Internal;

/**
 * This class performs some set of actions as the PlatformReservedUser
 * These functionalities are used by other internal APIs
 */

class WbStackPlatformReservedUser{
    const PLATFORM_RESERVED_USER = 'PlatformReservedUser';
    // TODO the email address should not be hardcoded? Use one from a mediawiki setting?
    const PLATFORM_RESERVED_EMAIL = 'PlatformReservedUser.mediawikiuser.platform@wbstack.com';

    public static function getUser() {
        return \User::newFromName( self::PLATFORM_RESERVED_USER );
    }

    public static function createIfNotExists() {
        $user = self::getUser();

        // Check the user doesn't already exist
        // TODO the user could be renamed, so check if # of users > 0 instead here..
        if($user->idForName() !== 0){
            // null as no action needed
            return null;
        }

        $user->setEmail( self::PLATFORM_RESERVED_EMAIL );

        // Create the user
        // TODO check create status?
        $createStatus = \MediaWiki\Auth\AuthManager::singleton()->autoCreateUser(
            $user,
            \MediaWiki\Auth\AuthManager::AUTOCREATE_SOURCE_MAINT,
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
            'oauthVersion' => '1.0',
            'oauth2IsConfidential' => false,
            'oauth2GrantTypes' => null
        ];

        $context = \RequestContext::getMain();
        $context->setUser( self::getUser() );

        $dbw = \MediaWiki\Extensions\OAuth\Backend\Utils::getCentralDB( DB_MASTER );
        $control = new \MediaWiki\Extensions\OAuth\Control\ConsumerSubmitControl( $context, $data, $dbw );
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
        $control = new \MediaWiki\Extensions\OAuth\Control\ConsumerSubmitControl( $context, $data, $dbw );
        $approveStatus = $control->submit();

        if ( !$approveStatus->isGood() ) {
            // TODO return more info...
            return false;
        }

        return true;
    }

    public static function getOAuthConsumer($consumerName, $version) {
        $user = self::getUser();
        // TODO create the oauth consumer on the fly if it doesn't exist (needs grants and callbackurl)

        // Bail if the user not registered..
        if($user->idForName() === 0){
            return false;
        }

        $db = \MediaWiki\Extensions\OAuth\Backend\Utils::getCentralDB( DB_REPLICA );

        // $c is a MWOAuthConsumer
        // https://github.com/wikimedia/mediawiki-extensions-OAuth/blob/master/includes/backend/MWOAuthConsumer.php
        $c = \MediaWiki\Extensions\OAuth\Backend\Consumer::newFromNameVersionUser(
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
            'consumerSecret' => \MediaWiki\Extensions\OAuth\Backend\Utils::hmacDBSecret( $c->getSecretKey() ),
        ];
    }
}
