<?php

use MediaWiki\MediaWikiServices;

/**
 * Special page
 *
 * @file
 * @ingroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013 Lost in Translations Inc.
 * @license GPL-2.0-or-later
 */

class SpecialInviteSignup extends SpecialPage {
	protected $groups;
	protected $store;

	public function __construct() {
		parent::__construct( 'InviteSignup', 'invitesignup' );
		global $wgISGroups;
		$this->groups = $wgISGroups;
	}

	public function doesWrites() {
		return true;
	}

	public function setStore( InviteStore $store ) {
		$this->store = $store;
	}

	protected function getStore() {
		if ( $this->store === null ) {
			$this->store = new InviteStore(
				MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY ),
				'invitesignup'
			);
		}

		return $this->store;
	}

	public function execute( $par ) {
		$this->checkPermissions();

		$request = $this->getRequest();
		$user = $this->getUser();
		$out = $this->getOutput();
		$this->setHeaders();

		$store = $this->getStore();

		$token = $request->getVal( 'token' );
		if ( $request->wasPosted() && $user->matchEditToken( $token, 'is' ) ) {
			if ( $request->getVal( 'do' ) === 'delete' ) {
				$store->deleteInvite( $request->getVal( 'hash' ) );
			}
			if ( $request->getVal( 'do' ) === 'add' ) {
				$email = $request->getVal( 'email' );
				$okay = Sanitizer::validateEmail( $email );
				if ( trim( $email ) === '' ) {
					// Silence
				} elseif ( !$okay ) {
					$out->wrapWikiMsg(
						Html::rawElement( 'div', [ 'class' => 'error' ], "$1" ),
						[ 'is-invalidemail', $email ]
					);
				} else {
					$groups = [];
					foreach ( $this->groups as $group ) {
						if ( $request->getCheck( "group-$group" ) ) {
							$groups[] = $group;
						}
					}
					$hash = $store->addInvite( $user, $email, $groups );
					self::sendInviteEmail( $user, $email, $hash );
				}
			}
		}

		$invites = $store->getInvites();
		$lang = $this->getLanguage();

		$out->addHTML(
			Html::openElement( 'table', [ 'class' => 'wikitable' ] ) .
			Html::openElement( 'thead' ) .
			Html::openElement( 'tr' ) .
			Html::element( 'th', null, $this->msg( 'is-tableth-date' )->text() ) .
			Html::element( 'th', null, $this->msg( 'is-tableth-email' )->text() ) .
			Html::element( 'th', null, $this->msg( 'is-tableth-inviter' )->text() ) .
			Html::element( 'th', null, $this->msg( 'is-tableth-signup' )->text() ) .
			Html::element( 'th', null, $this->msg( 'is-tableth-groups' )->text() ) .
			Html::element( 'th', null, '' ) .
			$this->getAddRow() .
			Html::closeElement( 'thead' )
		);
		foreach ( $invites as $hash => $invite ) {
			$whenSort = [ 'data-sort-value' => $invite['when'] ];
			$when = $lang->userTimeAndDate( $invite['when'], $user );
			$email = $invite['email'];
			$groups = $invite['groups'];
			if ( isset( $invite['invitee'] ) ) {
				$inviteeUser = User::newFromId( $invite['invitee'] );
				$name = $inviteeUser->getName();
				$email = "$name <$email>";
			} else {
				$name = '#';
			}
			foreach ( $groups as $i => $g ) {
				$groups[$i] = $lang->getGroupMemberName( $g, $name );
			}

			$groups = $lang->commaList( $groups );

			$out->addHTML(
				Html::openElement( 'tr' ) .
				Html::element( 'td', $whenSort, $when ) .
				Html::element( 'td', null, $email ) .
				Html::element( 'td', null, User::newFromId( $invite['inviter'] )->getName() ) .
				Html::element( 'td',
					[ 'data-sort-value' => $invite['used'] ],
					$invite['used'] ? $lang->userTimeAndDate( $invite['used'], $user ) : ''
				) .
				Html::element( 'td', null, $groups ) .
				Html::rawElement( 'td',
					null,
					$invite['used'] ? '' : $this->getDeleteButton( $invite['hash'] )
				) .
				Html::closeElement( 'tr' )
			);
		}
		$out->addHTML( '</table>' );
	}

	protected function getDeleteButton( $hash ) {
		$attribs = [
			'method' => 'post',
			'action' => $this->getPageTitle()->getLocalURL(),
		];
		$form = Html::openElement( 'form', $attribs );
		$form .= Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() );
		$form .= Html::hidden( 'token', $this->getUser()->getEditToken( 'is' ) );
		$form .= Html::hidden( 'hash', $hash );
		$form .= Html::hidden( 'do', 'delete' );
		$form .= Xml::submitButton( $this->msg( 'is-delete' )->text() );
		$form .= Html::closeElement( 'form' );

		return $form;
	}

	protected function getAddRow() {
		$user = $this->getUser();
		$lang = $this->getLanguage();

		$add =
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedDBkey() ) .
			Html::hidden( 'token', $user->getEditToken( 'is' ) ) .
			Html::hidden( 'do', 'add' ) .
			Xml::submitButton( $this->msg( 'is-add' )->text() );

		$attribs = [
			'method' => 'post',
			'action' => $this->getPageTitle()->getLocalURL(),
		];

		$groupChecks = [];
		foreach ( $this->groups as $group ) {
			$groupnameLocalized = $lang->getGroupMemberName( $group, '#' );

			// Username is not applicable
			$groupChecks[] = Xml::checkLabel(
				$groupnameLocalized,
				"group-$group",
				"group-$group"
			);
		}

		return Html::openElement( 'tr' ) .
			Html::openElement( 'form', $attribs ) .
			Html::element( 'td', null, $lang->userTimeAndDate( wfTimestamp(), $user ) ) .
			Html::rawElement( 'td', null, Xml::input( 'email' ) ) .
			Html::element( 'td', null, $user->getName() ) .
			Html::element( 'td', null, '' ) .
			Html::rawElement( 'td', null, implode( ' ', $groupChecks ) ) .
			Html::rawElement( 'td', null, $add ) .
			Html::closeElement( 'form' ) .
			Html::closeElement( 'tr' );
	}

	public static function sendInviteEmail( User $inviter, $email, $hash ) {
		global $wgPasswordSender;

		$url = Title::newFromText( 'Special:CreateAccount' )->getCanonicalURL(
			[ 'invite' => $hash ]
		);

		$subj = wfMessage( 'is-emailsubj' )->inContentLanguage();
		$body = wfMessage( 'is-emailbody' )
			->params( $inviter->getName(), $url )
			->inContentLanguage();

		$emailTo = new MailAddress( $email );
		$emailFrom = new MailAddress( $wgPasswordSender, wfMessage( 'emailsender' )->text() );

		MediaWikiServices::getInstance()->getEmailer()
			->send(
				[ $emailTo ],
				$emailFrom,
				$subj->text(),
				$body->text(),
				null,
				[ 'replyTo' => $emailFrom ]
			);
	}
}
