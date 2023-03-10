<?php

namespace MediaWiki\Extension\OAuth\Repository;

use InvalidArgumentException;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use MediaWiki\Extension\OAuth\Entity\AuthCodeEntity;

class AuthCodeRepository extends CacheRepository implements AuthCodeRepositoryInterface {

	/**
	 * Creates a new AuthCode
	 *
	 * @return AuthCodeEntityInterface
	 */
	public function getNewAuthCode() {
		return new AuthCodeEntity();
	}

	/**
	 * Persists a new auth code to permanent storage.
	 *
	 * @param AuthCodeEntityInterface $authCodeEntity
	 *
	 * @throws UniqueTokenIdentifierConstraintViolationException
	 */
	public function persistNewAuthCode( AuthCodeEntityInterface $authCodeEntity ) {
		if ( !$authCodeEntity instanceof AuthCodeEntity ) {
			throw new InvalidArgumentException(
				'$authCodeEntity must be instance of ' .
				AuthCodeEntity::class . ', got ' . get_class( $authCodeEntity ) . ' instead'
			);
		}
		if ( $this->has( $authCodeEntity->getIdentifier() ) ) {
			throw UniqueTokenIdentifierConstraintViolationException::create();
		}

		$this->set(
			$authCodeEntity->getIdentifier(),
			$authCodeEntity->jsonSerialize(),
			$authCodeEntity->getExpiryDateTime()->getTimestamp()
		);
	}

	/**
	 * Revoke an auth code.
	 *
	 * @param string $codeId
	 */
	public function revokeAuthCode( $codeId ) {
		$this->delete( $codeId );
	}

	/**
	 * Check if the auth code has been revoked.
	 *
	 * @param string $codeId
	 *
	 * @return bool Return true if this code has been revoked
	 */
	public function isAuthCodeRevoked( $codeId ) {
		return $this->has( $codeId ) === false;
	}

	/**
	 * Get object type for session key
	 *
	 * @return string
	 */
	protected function getCacheKeyType(): string {
		return 'AuthCode';
	}
}
