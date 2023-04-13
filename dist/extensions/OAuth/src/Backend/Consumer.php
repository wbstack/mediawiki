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

namespace MediaWiki\Extension\OAuth\Backend;

use FormatJson;
use IContextSource;
use Linker;
use LogicException;
use MediaWiki\Extension\OAuth\Entity\ClientEntity as OAuth2Client;
use MediaWiki\MediaWikiServices;
use Message;
use MWException;
use MWRestrictions;
use SpecialPage;
use User;
use WikiMap;
use Wikimedia\Rdbms\DBConnRef;

/**
 * Representation of an OAuth consumer.
 */
abstract class Consumer extends MWOAuthDAO {
	public const OAUTH_VERSION_1 = 1;
	public const OAUTH_VERSION_2 = 2;

	/** @var array Backwards-compatibility grant mappings */
	public static $mapBackCompatGrants = [
		'useoauth' => 'basic',
		'authonly' => 'mwoauth-authonly',
		'authonlyprivate' => 'mwoauth-authonlyprivate',
	];

	/** @var int Unique ID */
	protected $id;
	/** @var string Hex token */
	protected $consumerKey;
	/** @var string Name of connected application */
	protected $name;
	/** @var int Publisher's central user ID. $wgMWOAuthSharedUserIDs defines which central ID
	 *    provider to use.
	 */
	protected $userId;
	/** @var string Version used for handshake breaking changes */
	protected $version;
	/** @var string OAuth callback URL for authorization step */
	protected $callbackUrl;
	/**
	 * @var bool OAuth callback URL is a prefix and we allow all URLs which
	 *   have callbackUrl as the prefix
	 */
	protected $callbackIsPrefix;
	/** @var string Application description */
	protected $description;
	/** @var string Publisher email address */
	protected $email;
	/** @var string|null TS_MW timestamp of when email address was confirmed */
	protected $emailAuthenticated;
	/** @var bool User accepted the developer agreement */
	protected $developerAgreement;
	/** @var bool Consumer is for use by the owner only */
	protected $ownerOnly;
	/** @var int Version of the OAuth protocol */
	protected $oauthVersion;
	/** @var string Wiki ID the application can be used on (or "*" for all) */
	protected $wiki;
	/** @var string TS_MW timestamp of proposal */
	protected $registration;
	/** @var string Secret HMAC key */
	protected $secretKey;
	/** @var string Public RSA key */
	protected $rsaKey;
	/** @var array List of grants */
	protected $grants;
	/** @var \MWRestrictions IP restrictions */
	protected $restrictions;
	/** @var int MWOAuthConsumer::STAGE_* constant */
	protected $stage;
	/** @var string TS_MW timestamp of last stage change */
	protected $stageTimestamp;
	/** @var bool Indicates this consumer's information is suppressed */
	protected $deleted;
	/** @var bool Indicates whether the client (consumer) is able to keep the secret */
	protected $oauth2IsConfidential;
	/** @var array OAuth2 grant types available to the client */
	protected $oauth2GrantTypes;

	/* Stages that registered consumer takes (stored in DB) */
	public const STAGE_PROPOSED = 0;
	public const STAGE_APPROVED = 1;
	public const STAGE_REJECTED = 2;
	public const STAGE_EXPIRED  = 3;
	public const STAGE_DISABLED = 4;

	/**
	 * Maps stage ids to human-readable names which describe them as a state
	 * @var array
	 */
	public static $stageNames = [
		self::STAGE_PROPOSED => 'proposed',
		self::STAGE_REJECTED => 'rejected',
		self::STAGE_EXPIRED  => 'expired',
		self::STAGE_APPROVED => 'approved',
		self::STAGE_DISABLED => 'disabled',
	];

	/**
	 * Maps stage ids to human-readable names which describe them as an action (which would result
	 * in that stage)
	 * @var array
	 */
	public static $stageActionNames = [
		self::STAGE_PROPOSED => 'propose',
		self::STAGE_REJECTED => 'reject',
		self::STAGE_EXPIRED  => 'propose',
		self::STAGE_APPROVED => 'approve',
		self::STAGE_DISABLED => 'disable',
	];

	/**
	 * Get member => db field mapping
	 * Loads all fields to avoid unnecessary querying
	 *
	 * @return array
	 */
	protected static function getSchema() {
		return [
			'table'          => 'oauth_registered_consumer',
			'fieldColumnMap' => [
				'id'                    => 'oarc_id',
				'consumerKey'           => 'oarc_consumer_key',
				'name'                  => 'oarc_name',
				'userId'                => 'oarc_user_id',
				'version'               => 'oarc_version',
				'callbackUrl'           => 'oarc_callback_url',
				'callbackIsPrefix'      => 'oarc_callback_is_prefix',
				'description'           => 'oarc_description',
				'email'                 => 'oarc_email',
				'emailAuthenticated'    => 'oarc_email_authenticated',
				'oauthVersion'          => 'oarc_oauth_version',
				'developerAgreement'    => 'oarc_developer_agreement',
				'ownerOnly'             => 'oarc_owner_only',
				'wiki'                  => 'oarc_wiki',
				'grants'                => 'oarc_grants',
				'registration'          => 'oarc_registration',
				'secretKey'             => 'oarc_secret_key',
				'rsaKey'                => 'oarc_rsa_key',
				'restrictions'          => 'oarc_restrictions',
				'stage'                 => 'oarc_stage',
				'stageTimestamp'        => 'oarc_stage_timestamp',
				'deleted'               => 'oarc_deleted',
				'oauth2IsConfidential'  => 'oarc_oauth2_is_confidential',
				'oauth2GrantTypes'      => 'oarc_oauth2_allowed_grants',
			],
			'idField'        => 'id',
			'autoIncrField'  => 'id',
		];
	}

	protected static function getFieldPermissionChecks() {
		return [
			'name'             => 'userCanSee',
			'userId'           => 'userCanSee',
			'version'          => 'userCanSee',
			'callbackUrl'      => 'userCanSee',
			'callbackIsPrefix' => 'userCanSee',
			'description'      => 'userCanSee',
			'rsaKey'           => 'userCanSee',
			'email'            => 'userCanSeeEmail',
			'secretKey'        => 'userCanSeeSecret',
			'restrictions'     => 'userCanSeePrivate',
		];
	}

	/**
	 * @param array $data
	 * @return string
	 */
	protected static function getConsumerClass( array $data ) {
		return static::isOAuth2( $data ) ?
			OAuth2Client::class :
			OAuth1Consumer::class;
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	protected static function isOAuth2( array $data = [] ) {
		$oauthVersion = $data['oarc_oauth_version'] ?? $data['oauthVersion'];
		return (int)$oauthVersion === self::OAUTH_VERSION_2;
	}

	/**
	 * @param DBConnRef $db
	 * @param string|null $key
	 * @param int $flags MWOAuthConsumer::READ_* bitfield
	 * @return Consumer|false
	 */
	public static function newFromKey( DBConnRef $db, $key, $flags = 0 ) {
		$row = $db->selectRow( static::getTable(),
			array_values( static::getFieldColumnMap() ),
			[ 'oarc_consumer_key' => (string)$key ],
			__METHOD__,
			( $flags & self::READ_LOCKING ) ? [ 'FOR UPDATE' ] : []
		);

		if ( $row ) {
			return static::newFromRow( $db, $row );
		} else {
			return false;
		}
	}

	/**
	 * @param DBConnRef $db
	 * @param string $name
	 * @param string $version
	 * @param int $userId Central user ID
	 * @param int $flags MWOAuthConsumer::READ_* bitfield
	 * @return Consumer|bool
	 */
	public static function newFromNameVersionUser(
		DBConnRef $db, $name, $version, $userId, $flags = 0
	) {
		$row = $db->selectRow( static::getTable(),
			array_values( static::getFieldColumnMap() ),
			[
				'oarc_name' => (string)$name,
				'oarc_version' => (string)$version,
				'oarc_user_id' => (int)$userId
			],
			__METHOD__,
			( $flags & self::READ_LOCKING ) ? [ 'FOR UPDATE' ] : []
		);

		if ( $row ) {
			return static::newFromRow( $db, $row );
		} else {
			return false;
		}
	}

	/**
	 * @return array
	 */
	public static function newGrants() {
		return [];
	}

	/**
	 * @return array
	 */
	public static function getAllStages() {
		return [
			self::STAGE_PROPOSED,
			self::STAGE_REJECTED,
			self::STAGE_EXPIRED,
			self::STAGE_APPROVED,
			self::STAGE_DISABLED,
		];
	}

	/**
	 * Internal ID (DB primary key).
	 * @return int
	 */
	public function getId() {
		return $this->get( 'id' );
	}

	/**
	 * Consumer key (32-character hexadecimal string that's used in the OAuth protocol
	 * and in URLs). This is used as the consumer ID for most external purposes.
	 * @return string
	 */
	public function getConsumerKey() {
		return $this->get( 'consumerKey' );
	}

	/**
	 * Name of the consumer.
	 * @return string
	 */
	public function getName() {
		return $this->get( 'name' );
	}

	/**
	 * Central ID of the owner.
	 * @return int
	 */
	public function getUserId() {
		return $this->get( 'userId' );
	}

	/**
	 * Consumer version. This is mostly meant for humans: different versions of the same
	 * application have different keys and are handled as different consumers internally.
	 * @return string
	 */
	public function getVersion() {
		return $this->get( 'version' );
	}

	/**
	 * Callback URL (or prefix). The browser will be redirected to this URL at the end of
	 * an OAuth handshake. See getCallbackIsPrefix() for the interpretation of this field.
	 * @return string
	 */
	public function getCallbackUrl() {
		return $this->get( 'callbackUrl' );
	}

	/**
	 * When false, the callback URL will be determined by getCallbackUrl(). When true,
	 * getCallbackUrl() returns a prefix; the callback URL must be provided by the caller
	 * and must match the prefix. For the exact definition of "match", see
	 * MWOAuthServer::checkCallback().
	 * @return bool
	 */
	public function getCallbackIsPrefix() {
		return $this->get( 'callbackIsPrefix' );
	}

	/**
	 * Description of the consumer. Currently interpreted as plain text; might change to wikitext
	 * in the future.
	 * @return string
	 */
	public function getDescription() {
		return $this->get( 'description' );
	}

	/**
	 * Email address of the owner.
	 * @return string
	 */
	public function getEmail() {
		return $this->get( 'email' );
	}

	/**
	 * Date of verifying the email, in TS_MW format. In practice this will be the same as
	 * getRegistration().
	 * @return string|null
	 */
	public function getEmailAuthenticated() {
		return $this->get( 'emailAuthenticated' );
	}

	/**
	 * Did the user accept the developer agreement (the terms of use checkbox at the bottom of the
	 * registration form)? Except for very old users, always true.
	 * @return bool
	 */
	public function getDeveloperAgreement() {
		return $this->get( 'developerAgreement' );
	}

	/**
	 * Owner-only consumers will use one-legged flow instead of three-legged (see
	 * https://github.com/Mashape/mashape-oauth/blob/master/FLOWS.md#oauth-10a-one-legged ); there
	 * is only one user (who is the same as the owner) and they learn the access token at
	 * consumer registration time.
	 * @return bool
	 */
	public function getOwnerOnly() {
		return $this->get( 'ownerOnly' );
	}

	/**
	 * @return int
	 */
	abstract public function getOAuthVersion();

	/**
	 * The wiki on which the consumer is allowed to access user accounts. A wiki ID or '*' for all.
	 * @return string
	 */
	public function getWiki() {
		return $this->get( 'wiki' );
	}

	/**
	 * The list of grants required by this application.
	 * @return string[]
	 */
	public function getGrants() {
		return $this->get( 'grants' );
	}

	/**
	 * Consumer registration date in TS_MW format.
	 * @return string
	 */
	public function getRegistration() {
		return $this->get( 'registration' );
	}

	/**
	 * Secret key used to derive the consumer secret for HMAC-SHA1 signed OAuth requests.
	 * The actual consumer secret will be calculated via Utils::hmacDBSecret() to mitigate
	 * DB leaks.
	 * @return string
	 */
	public function getSecretKey() {
		return $this->get( 'secretKey' );
	}

	/**
	 * Public RSA key for RSA-SHA1 signed OAuth requests.
	 * @return string
	 */
	public function getRsaKey() {
		return $this->get( 'rsaKey' );
	}

	/**
	 * Application restrictions (such as allowed IPs).
	 * @return MWRestrictions
	 */
	public function getRestrictions() {
		return $this->get( 'restrictions' );
	}

	/**
	 * Stage at which the consumer is in the review workflow (proposed, approved etc).
	 * @return int One of the STAGE_* constants
	 */
	public function getStage() {
		return $this->get( 'stage' );
	}

	/**
	 * Date at which the consumer was moved to the current stage, in TS_MW format.
	 * @return string
	 */
	public function getStageTimestamp() {
		return $this->get( 'stageTimestamp' );
	}

	/**
	 * Is the consumer suppressed? (There is no plain deletion; the closest equivalent is the
	 * rejected/disabled stage.)
	 * @return bool
	 */
	public function getDeleted() {
		return $this->get( 'deleted' );
	}

	/**
	 * @param MWOAuthDataStore $dataStore
	 * @param string $verifyCode verification code
	 * @param string $requestKey original request key from /initiate
	 * @return string the url for redirection
	 */
	public function generateCallbackUrl( $dataStore, $verifyCode, $requestKey ) {
		$callback = $dataStore->getCallbackUrl( $this->key, $requestKey );

		if ( $callback === 'oob' ) {
		  $callback = $this->getCallbackUrl();
		}

		return wfAppendQuery( $callback, [
			'oauth_verifier' => $verifyCode,
			'oauth_token'    => $requestKey
		] );
	}

	/**
	 * Attempts to find an authorization by this user for this consumer. Since a user can
	 * accept a consumer multiple times (once for "*" and once for each specific wiki),
	 * there can several access tokens per-wiki (with varying grants) for a consumer.
	 * This will choose the most wiki-specific access token. The precedence is:
	 * a) The acceptance for wiki X if the consumer is applicable only to wiki X
	 * b) The acceptance for wiki $wikiId (if the consumer is applicable to it)
	 * c) The acceptance for wikis "*" (all wikis)
	 *
	 * Users might want more grants on some wikis than on "*". Note that the reverse would not
	 * make sense, since the consumer could just use the "*" acceptance if it has more grants.
	 *
	 * @param User $mwUser (local wiki user) User who may or may not have authorizations
	 * @param string $wikiId
	 * @throws MWOAuthException
	 * @return ConsumerAcceptance|bool
	 */
	public function getCurrentAuthorization( User $mwUser, $wikiId ) {
		$dbr = Utils::getCentralDB( DB_REPLICA );

		$centralUserId = Utils::getCentralIdFromLocalUser( $mwUser );
		if ( !$centralUserId ) {
			throw new MWOAuthException(
				'mwoauthserver-invalid-user',
				[
					$this->getName(),
					Message::rawParam(
						Linker::makeExternalLink(
							'https://www.mediawiki.org/wiki/Help:OAuth/Errors#E008',
							'E008',
							true
						)
					)
				]
			);
		}

		$checkWiki = $this->getWiki() !== '*' ? $this->getWiki() : $wikiId;

		$cmra = ConsumerAcceptance::newFromUserConsumerWiki(
			$dbr,
			$centralUserId,
			$this,
			$checkWiki,
			0,
			$this->getOAuthVersion()
		);
		if ( !$cmra ) {
			$cmra = ConsumerAcceptance::newFromUserConsumerWiki(
				$dbr,
				$centralUserId,
				$this,
				'*',
				0,
				$this->getOAuthVersion()
			);
		}
		return $cmra;
	}

	/**
	 * @param User $mwUser
	 * @param bool $update
	 * @param array $grants
	 * @param string|null $requestTokenKey
	 * @return mixed
	 */
	abstract public function authorize( User $mwUser, $update, $grants, $requestTokenKey = null );

	/**
	 * Verify that this user can authorize this consumer
	 *
	 * @param User $mwUser
	 * @throws MWOAuthException
	 * @throws MWException
	 */
	protected function conductAuthorizationChecks( User $mwUser ) {
		global $wgBlockDisablesLogin;

		// Check that user and consumer are in good standing
		if ( $mwUser->isLocked() || $wgBlockDisablesLogin && $mwUser->getBlock() ) {
			throw new MWOAuthException( 'mwoauthserver-insufficient-rights', [
				Message::rawParam( Linker::makeExternalLink(
					'https://www.mediawiki.org/wiki/Help:OAuth/Errors#E007',
					'E007',
					true
				) )
			] );
		}

		if ( $this->getDeleted() ) {
			throw new MWOAuthException( 'mwoauthserver-bad-consumer-key', [
				Message::rawParam( Linker::makeExternalLink(
					'https://www.mediawiki.org/wiki/Help:OAuth/Errors#E006',
					'E006',
					true
				) )
			] );
		} elseif ( !$this->isUsableBy( $mwUser ) ) {
			$owner = Utils::getCentralUserNameFromId(
				$this->getUserId(),
				$mwUser
			);
			throw new MWOAuthException(
				'mwoauthserver-bad-consumer',
				[ $this->getName(), Utils::getCentralUserTalk( $owner ), Message::rawParam(
					Linker::makeExternalLink(
						'https://www.mediawiki.org/wiki/Help:OAuth/Errors#E005',
						'E005',
						true
					)
				) ]
			);
		} elseif ( $this->getOwnerOnly() ) {
			throw new MWOAuthException( 'mwoauthserver-consumer-owner-only', [
				$this->getName(),
				SpecialPage::getTitleFor(
					'OAuthConsumerRegistration', 'update/' . $this->getConsumerKey()
				),
				Message::rawParam( Linker::makeExternalLink(
					'https://www.mediawiki.org/wiki/Help:OAuth/Errors#E010',
					'E010',
					true
				) )
			] );
		}
	}

	/**
	 * @param User $mwUser
	 * @param bool $update
	 * @param array $grants
	 * @return ConsumerAcceptance
	 * @throws MWOAuthException
	 * @throws MWException
	 */
	protected function saveAuthorization( User $mwUser, $update, $grants ) {
		// CentralAuth may abort here if there is no global account for this user
		$centralUserId = Utils::getCentralIdFromLocalUser( $mwUser );
		if ( !$centralUserId ) {
			throw new MWOAuthException(
				'mwoauthserver-invalid-user',
				[
					$this->getName(),
					Message::rawParam(
						Linker::makeExternalLink(
							'https://www.mediawiki.org/wiki/Help:OAuth/Errors#E008',
							'E008',
							true
						)
					)
				]
			);
		}

		$dbw = Utils::getCentralDB( DB_PRIMARY );
		// Check if this authorization exists
		$cmra = $this->getCurrentAuthorization( $mwUser, WikiMap::getCurrentWikiId() );

		if ( $update ) {
			// This should be an update to an existing authorization
			if ( !$cmra ) {
				// update requested, but no existing key
				throw new MWOAuthException( 'mwoauthserver-invalid-request' );
			}
			$cmra->setFields( [
				'wiki'   => $this->getWiki(),
				'grants' => $grants
			] );
			$cmra->save( $dbw );
		} elseif ( !$cmra ) {
			// Add the Authorization to the database
			$accessToken = MWOAuthDataStore::newToken();
			$cmra = ConsumerAcceptance::newFromArray( [
				'id'           => null,
				'wiki'         => $this->getWiki(),
				'userId'       => $centralUserId,
				'consumerId'   => $this->getId(),
				'accessToken'  => $accessToken->key,
				'accessSecret' => $accessToken->secret,
				'grants'       => $grants,
				'accepted'     => wfTimestampNow(),
				'oauth_version' => $this->getOAuthVersion()
			] );
			$cmra->save( $dbw );
		}

		return $cmra;
	}

	/**
	 * Check if the consumer is usable by $user
	 *
	 * "Usable by $user" includes:
	 * - Approved for multi-user use
	 * - Approved for owner-only use and is owned by $user
	 * - Still pending approval and is owned by $user
	 *
	 * @param User $user
	 * @return bool
	 */
	public function isUsableBy( User $user ) {
		if ( $this->stage === self::STAGE_APPROVED && !$this->getOwnerOnly() ) {
			return true;
		} elseif ( $this->stage === self::STAGE_PROPOSED || $this->stage === self::STAGE_APPROVED ) {
			$centralId = Utils::getCentralIdFromLocalUser( $user );
			return ( $centralId && $this->userId === $centralId );
		}

		return false;
	}

	protected function normalizeValues() {
		// Keep null values since we're constructing w/ them to auto-increment
		$this->id = $this->id === null ? null : (int)$this->id;
		$this->userId = (int)$this->userId;
		$this->registration = wfTimestamp( TS_MW, $this->registration );
		$this->stage = (int)$this->stage;
		$this->stageTimestamp = wfTimestamp( TS_MW, $this->stageTimestamp );
		$this->emailAuthenticated = wfTimestampOrNull( TS_MW, $this->emailAuthenticated );
		$this->grants = (array)$this->grants;
		$this->callbackIsPrefix = (bool)$this->callbackIsPrefix;
		$this->ownerOnly = (bool)$this->ownerOnly;
		$this->oauthVersion = (int)$this->oauthVersion;
		$this->developerAgreement = (bool)$this->developerAgreement;
		$this->deleted = (bool)$this->deleted;
		$this->oauth2IsConfidential = (bool)$this->oauth2IsConfidential;
	}

	protected function encodeRow( DBConnRef $db, $row ) {
		// For compatibility with other wikis in the farm, un-remap some grants
		foreach ( self::$mapBackCompatGrants as $old => $new ) {
			while ( ( $i = array_search( $new, $row['oarc_grants'], true ) ) !== false ) {
				$row['oarc_grants'][$i] = $old;
			}
		}

		$row['oarc_registration'] = $db->timestamp( $row['oarc_registration'] );
		$row['oarc_stage_timestamp'] = $db->timestamp( $row['oarc_stage_timestamp'] );
		$row['oarc_restrictions'] = $row['oarc_restrictions']->toJson();
		$row['oarc_grants'] = FormatJson::encode( $row['oarc_grants'] );
		$row['oarc_email_authenticated'] =
			$db->timestampOrNull( $row['oarc_email_authenticated'] );
		$row['oarc_oauth2_allowed_grants'] = FormatJson::encode(
			$row['oarc_oauth2_allowed_grants']
		);
		return $row;
	}

	protected function decodeRow( DBConnRef $db, $row ) {
		$row['oarc_registration'] = wfTimestamp( TS_MW, $row['oarc_registration'] );
		$row['oarc_stage'] = (int)$row['oarc_stage'];
		$row['oarc_stage_timestamp'] = wfTimestamp( TS_MW, $row['oarc_stage_timestamp'] );
		$row['oarc_restrictions'] = MWRestrictions::newFromJson( $row['oarc_restrictions'] );
		$row['oarc_grants'] = FormatJson::decode( $row['oarc_grants'], true );
		$row['oarc_user_id'] = (int)$row['oarc_user_id'];
		$row['oarc_email_authenticated'] =
			wfTimestampOrNull( TS_MW, $row['oarc_email_authenticated'] );
		$row['oarc_oauth2_allowed_grants'] = FormatJson::decode(
			$row['oarc_oauth2_allowed_grants'] ?? 'null', true
		);

		// For backwards compatibility, remap some grants
		foreach ( self::$mapBackCompatGrants as $old => $new ) {
			while ( ( $i = array_search( $old, $row['oarc_grants'], true ) ) !== false ) {
				$row['oarc_grants'][$i] = $new;
			}
		}

		return $row;
	}

	/**
	 * Magic method so that fields like $consumer->secret and $consumer->key work.
	 * This allows MWOAuthConsumer to be a replacement for OAuthConsumer
	 * in lib/OAuth.php without inheriting.
	 * @param mixed $prop
	 * @return mixed
	 */
	public function __get( $prop ) {
		if ( $prop === 'key' ) {
			return $this->consumerKey;
		} elseif ( $prop === 'secret' ) {
			return Utils::hmacDBSecret( $this->secretKey );
		} elseif ( $prop === 'callback_url' ) {
			return $this->callbackUrl;
		} else {
			throw new LogicException( 'Direct property access attempt: ' . $prop );
		}
	}

	protected function userCanSee( $name, IContextSource $context ) {
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		if ( $this->getDeleted()
			&& !$permissionManager->userHasRight( $context->getUser(), 'mwoauthviewsuppressed' )
		) {
			return $context->msg( 'mwoauth-field-hidden' );
		} else {
			return true;
		}
	}

	protected function userCanSeePrivate( $name, IContextSource $context ) {
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		if ( !$permissionManager->userHasRight( $context->getUser(), 'mwoauthviewprivate' ) ) {
			return $context->msg( 'mwoauth-field-private' );
		} else {
			return $this->userCanSee( $name, $context );
		}
	}

	protected function userCanSeeEmail( $name, IContextSource $context ) {
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		if ( !$permissionManager->userHasRight( $context->getUser(), 'mwoauthmanageconsumer' ) ) {
			return $context->msg( 'mwoauth-field-private' );
		} else {
			return $this->userCanSee( $name, $context );
		}
	}

	protected function userCanSeeSecret( $name, IContextSource $context ) {
		return $context->msg( 'mwoauth-field-private' );
	}
}
