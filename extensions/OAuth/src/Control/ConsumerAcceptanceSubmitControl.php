<?php

/**
 * (c) Aaron Schulz 2013, GPL
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

namespace MediaWiki\Extensions\OAuth\Control;

use MediaWiki\Extensions\OAuth\Backend\Consumer;
use MediaWiki\Extensions\OAuth\Backend\ConsumerAcceptance;
use MediaWiki\Extensions\OAuth\Backend\MWOAuthException;
use MediaWiki\Extensions\OAuth\Backend\Utils;
use MediaWiki\Extensions\OAuth\Lib\OAuthException;
use MediaWiki\Extensions\OAuth\Repository\AccessTokenRepository;
use MediaWiki\Logger\LoggerFactory;
use Wikimedia\Rdbms\DBConnRef;

/**
 * This handles the core logic of submitting/approving application
 * consumer requests and the logic of managing approved consumers
 *
 * This control can be used on any wiki, not just the management one
 *
 * @TODO: improve error messages
 */
class ConsumerAcceptanceSubmitControl extends SubmitControl {
	/** @var DBConnRef */
	protected $dbw;

	/** @var int */
	protected $oauthVersion;

	/**
	 * @param \IContextSource $context
	 * @param array $params
	 * @param DBConnRef $dbw Result of MWOAuthUtils::getCentralDB( DB_MASTER )
	 * @param int $oauthVersion
	 */
	public function __construct(
		\IContextSource $context, array $params, DBConnRef $dbw, $oauthVersion
	) {
		parent::__construct( $context, $params );
		$this->dbw = $dbw;
		$this->oauthVersion = (int)$oauthVersion;
	}

	protected function getRequiredFields() {
		$required = [
			'update'   => [
				'acceptanceId' => '/^\d+$/',
				'grants'      => function ( $s ) {
					$grants = \FormatJson::decode( $s, true );
					return is_array( $grants ) && Utils::grantsAreValid( $grants );
				}
			],
			'renounce' => [
				'acceptanceId' => '/^\d+$/',
			],
		];
		if ( $this->isOAuth2() ) {
			$required['accept'] = [
				'client_id' => '/^[0-9a-f]{32}$/',
				'confirmUpdate' => '/^[01]$/',
			];
		} else {
			$required['accept'] = [
				'consumerKey'   => '/^[0-9a-f]{32}$/',
				'requestToken'  => '/^[0-9a-f]{32}$/',
				'confirmUpdate' => '/^[01]$/',
			];
		}

		return $required;
	}

	protected function checkBasePermissions() {
		$user = $this->getUser();
		if ( !$user->getID() ) {
			return $this->failure( 'not_logged_in', 'badaccess-group0' );
		} elseif ( !$user->isAllowed( 'mwoauthmanagemygrants' ) ) {
			return $this->failure( 'permission_denied', 'badaccess-group0' );
		} elseif ( wfReadOnly() ) {
			return $this->failure( 'readonly', 'readonlytext', wfReadOnlyReason() );
		}
		return $this->success();
	}

	protected function processAction( $action ) {
		$user = $this->getUser(); // proposer or admin
		$dbw = $this->dbw; // convenience

		$centralUserId = Utils::getCentralIdFromLocalUser( $user );
		if ( !$centralUserId ) { // sanity
			return $this->failure( 'permission_denied', 'badaccess-group0' );
		}

		switch ( $action ) {
		case 'accept':
			$payload = [];
			$identifier = $this->isOAuth2() ? 'client_id' : 'consumerKey';
			$cmr = Consumer::newFromKey( $this->dbw, $this->vals[$identifier] );
			if ( !$cmr ) {
				return $this->failure( 'invalid_consumer_key', 'mwoauth-invalid-consumer-key' );
			} elseif ( !$cmr->isUsableBy( $user ) ) {
				return $this->failure( 'permission_denied', 'badaccess-group0' );
			}

			try {
				if ( $this->isOAuth2() ) {
					$scopes = isset( $this->vals['scope'] ) ? explode( ' ', $this->vals['scope'] ) : [];
					$payload = $cmr->authorize( $this->getUser(), (bool)$this->vals['confirmUpdate'], $scopes );
				} else {
					$callback = $cmr->authorize(
						$this->getUser(),
						(bool)$this->vals[ 'confirmUpdate' ],
						$cmr->getGrants(),
						$this->vals[ 'requestToken' ]
					);
					$payload = [ 'callbackUrl' => $callback ];
				}
			} catch ( MWOAuthException $exception ) {
				return $this->failure( 'oauth_exception', $exception->msg, $exception->params );
			} catch ( OAuthException $exception ) {
				return $this->failure( 'oauth_exception',
					'mwoauth-oauth-exception', $exception->getMessage() );
			}

			LoggerFactory::getInstance( 'OAuth' )->info(
				'{user} performed action {action} on consumer {consumer}', [
					'action' => 'accept',
					'user' => $user->getName(),
					'consumer' => $cmr->getConsumerKey(),
					'target' => Utils::getCentralUserNameFromId( $cmr->getUserId(), 'raw' ),
					'comment' => '',
					'clientip' => $this->getContext()->getRequest()->getIP(),
				]
			);

			return $this->success( $payload );
		case 'update':
			$cmra = ConsumerAcceptance::newFromId( $dbw, $this->vals['acceptanceId'] );
			if ( !$cmra ) {
				return $this->failure( 'invalid_access_token', 'mwoauth-invalid-access-token' );
			} elseif ( $cmra->getUserId() !== $centralUserId ) {
				return $this->failure( 'invalid_access_token', 'mwoauth-invalid-access-token' );
			}
			$cmr = Consumer::newFromId( $dbw, $cmra->getConsumerId() );

			$grants = \FormatJson::decode( $this->vals['grants'], true ); // requested grants
			$grants = array_unique( array_intersect(
				array_merge(
					\MWGrants::getHiddenGrants(), // implied grants
					$grants // requested grants
				),
				 $cmr->getGrants() // Only keep the applicable ones
			) );

			LoggerFactory::getInstance( 'OAuth' )->info(
				'{user} performed action {action} on consumer {consumer}', [
					'action' => 'update-acceptance',
					'user' => $user->getName(),
					'consumer' => $cmr->getConsumerKey(),
					'target' => Utils::getCentralUserNameFromId( $cmr->getUserId(), 'raw' ),
					'comment' => '',
					'clientip' => $this->getContext()->getRequest()->getIP(),
				]
			);
			$cmra->setFields( [
				'grants' => array_intersect( $grants, $cmr->getGrants() ) // sanity
			] );
			$cmra->save( $dbw );

			return $this->success( $cmra );
		case 'renounce':
			$cmra = ConsumerAcceptance::newFromId( $dbw, $this->vals['acceptanceId'] );
			if ( !$cmra ) {
				return $this->failure( 'invalid_access_token', 'mwoauth-invalid-access-token' );
			} elseif ( $cmra->getUserId() !== $centralUserId ) {
				return $this->failure( 'invalid_access_token', 'mwoauth-invalid-access-token' );
			}

			$cmr = Consumer::newFromId( $dbw, $cmra->get( 'consumerId' ) );
			LoggerFactory::getInstance( 'OAuth' )->info(
				'{user} performed action {action} on consumer {consumer}', [
					'action' => 'renounce',
					'user' => $user->getName(),
					'consumer' => $cmr->getConsumerKey(),
					'target' => Utils::getCentralUserNameFromId( $cmr->getUserId(), 'raw' ),
					'comment' => '',
					'clientip' => $this->getContext()->getRequest()->getIP(),
				]
			);

			if ( $cmr->getOAuthVersion() === Consumer::OAUTH_VERSION_2 ) {
				$this->removeOAuth2AccessTokens( $cmra->getId() );
			}
			$cmra->delete( $dbw );

			return $this->success( $cmra );
		}
	}

	/**
	 * Convenience function
	 *
	 * @return bool
	 */
	private function isOAuth2() {
		return $this->oauthVersion === Consumer::OAUTH_VERSION_2;
	}

	/**
	 * @param int $approvalId
	 */
	private function removeOAuth2AccessTokens( $approvalId ) {
		$accessTokenRepository = new AccessTokenRepository();
		$accessTokenRepository->deleteForApprovalId( $approvalId );
	}
}
