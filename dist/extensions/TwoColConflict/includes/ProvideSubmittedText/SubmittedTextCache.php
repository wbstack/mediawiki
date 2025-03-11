<?php

namespace TwoColConflict\ProvideSubmittedText;

use MediaWiki\Session\SessionId;
use MediaWiki\User\UserIdentity;
use UnexpectedValueException;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SubmittedTextCache {

	private const CACHE_KEY = 'twoColConflict_yourText';

	private BagOStuff $cache;

	public function __construct( BagOStuff $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @param string $titleDbKey
	 * @param UserIdentity $user
	 * @param SessionId|null $sessionId
	 * @param string $text
	 *
	 * @return bool If caching was successful or not.
	 */
	public function stashText( string $titleDbKey, UserIdentity $user, ?SessionId $sessionId, string $text ): bool {
		$key = $this->makeCacheKey( $titleDbKey, $user, $sessionId );
		return $this->cache->set( $key, $text, ExpirationAwareness::TTL_DAY );
	}

	/**
	 * @param string $titleDbKey
	 * @param UserIdentity $user
	 * @param SessionId|null $sessionId
	 *
	 * @return string|false Returns false when the cache expired
	 */
	public function fetchText( string $titleDbKey, UserIdentity $user, ?SessionId $sessionId ) {
		$key = $this->makeCacheKey( $titleDbKey, $user, $sessionId );
		return $this->cache->get( $key );
	}

	private function makeCacheKey( string $titleDbKey, UserIdentity $user, ?SessionId $sessionId ): string {
		$components = [
			self::CACHE_KEY,
			$titleDbKey,
			$user->getId(),
		];
		// The user ID is specific enough for registered users
		if ( !$user->isRegistered() ) {
			if ( !$sessionId ) {
				throw new UnexpectedValueException( 'Must provide a session for anonymous users' );
			}
			// Warning, the session ID should not use the same spot as the user ID
			$components[] = $sessionId->getId();
		}
		return $this->cache->makeKey( ...$components );
	}

}
