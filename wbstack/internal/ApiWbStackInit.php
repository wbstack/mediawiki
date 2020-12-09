<?php

/**
 * This API is called when a wiki is first created.
 * It creates a user account from an name and email, sending that user a reset email (including temporary password)
 *
 * This code is a combination of maintenance/createAndPromote.php and SpecialPasswordReset.php ideas
 */

class ApiWbStackInit extends ApiBase {
    public function mustBePosted() {return true;}
    public function isWriteMode() {return true;}
    public function isInternal() {return true;}
    public function execute() {
        $this->executeWikiUser();
        WbStackPlatformReservedUser::createIfNotExists();
    }
    public function executeWikiUser() {
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
