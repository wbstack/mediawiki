<?php

use MediaWiki\MediaWikiServices;

class InviteSignupHooks {
	public static function onBeforeInitialize(
		Title $title,
		&$unused,
		&$output,
		&$user,
		WebRequest $request
	) {
		if ( !$title->isSpecialPage() ) {
			return true;
		}

		[ $name ] = MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->resolveAlias( $title->getDBkey() );

		if ( $name !== 'CreateAccount' ) {
			return true;
		}

		$hash = $request->getVal( 'invite', $request->getCookie( 'invite' ) );
		if ( $hash ) {
			$store = new InviteStore(
				MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA ),
				'invitesignup'
			);

			$invite = $store->getInvite( $hash );
			if ( $invite && $invite['used'] === null ) {
				global $wgInviteSignupHash;
				$wgInviteSignupHash = $hash;
				$request->response()->setCookie( 'invite', $hash );

				// Ensure user is allowed to register without entering email
				global $wgEmailConfirmToEdit;
				$wgEmailConfirmToEdit = false;

				// Make sure user can access the CreateAccount page
				global $wgWhitelistRead;
				if ( !is_array( $wgWhitelistRead ) ) {
					$wgWhitelistRead = [];
				}

				$wgWhitelistRead[] = $title->getPrefixedText();
			}
		}
	}

	public static function onUserGetRights( $user, &$rights ) {
		global $wgInviteSignupHash;
		if ( $wgInviteSignupHash === null ) {
			return true;
		}
		$rights[] = 'createaccount';

		// explicitly set read right
		$rights[] = 'read';
	}

	public static function onUserCreateForm( &$template ) {
		global $wgInviteSignupHash;
		if ( $wgInviteSignupHash === null ) {
			return true;
		}
		$template->data['link'] = null;
		$template->data['useemail'] = false;
	}

	public static function onAddNewAccount( User $user ) {
		global $wgInviteSignupHash;
		if ( $wgInviteSignupHash === null ) {
			return true;
		}

		$store = new InviteStore(
			MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY ),
			'invitesignup'
		);

		$invite = $store->getInvite( $wgInviteSignupHash );

		MediaWikiServices::getInstance()
			->getUserOptionsManager()
			->setOption( $user, 'is-inviter', $invite['inviter'] );

		$user->setEmail( $invite['email'] );
		$user->confirmEmail();
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		foreach ( $invite['groups'] as $group ) {
			$userGroupManager->addUserToGroup( $user, $group );
		}
		$user->saveSettings();
		$store->addSignupDate( $user, $wgInviteSignupHash );
		global $wgRequest;
		$wgRequest->response()->setCookie( 'invite', '', time() - 86400 );
	}

	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$dir = dirname( __DIR__ ) . '/sql';
		$type = $updater->getDB()->getType();

		$supportedTypes = [ 'mysql', 'postgres', 'sqlite' ];
		if ( !in_array( $type, $supportedTypes ) ) {
			throw new MWException( "InviteSignup does not support $type yet." );
		}

		$updater->addExtensionTable( 'invitesignup', "$dir/$type/invitesignup.sql" );
	}
}
