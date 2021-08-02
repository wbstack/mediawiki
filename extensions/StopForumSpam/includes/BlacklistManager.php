<?php
/**
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
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * https://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\StopForumSpam;

use Wikimedia\IPUtils;

class BlacklistManager {

	/**
	 * How long the confidence level should be cached for (1 day)
	 */
	private const CACHE_DURATION = 86400;

	/**
	 * Used in determining cache keys
	 * Not const due to the fact this changes based on whether PHP is 32-bit or 64-bit
	 */
	protected static $SHIFT_AMOUNT = null;
	protected static $BUCKET_MASK;
	protected static $OFFSET_MASK;

	/**
	 * @return bool true if blacklist has not expired
	 */
	public static function isBlacklistUpToDate() {
		global $wgMemc;

		return $wgMemc->get( self::getBlacklistKey() ) !== false
			&& $wgMemc->get( self::getBlacklistUpdateStateKey() ) === false;
	}

	/**
	 * Returns key for main blacklist
	 * @return string
	 */
	public static function getBlacklistKey() {
		return 'sfs:blacklist:set';
	}

	/**
	 * Get memcached key
	 * @param int $bucket
	 * @return string
	 * @private This is only public so SFSBlacklistUpdate::execute can access it
	 */
	public static function getIPBlacklistKey( $bucket ) {
		return 'sfs:blacklisted:' . $bucket;
	}

	/**
	 * Returns key for BlacklistUpdate state
	 * @return string
	 * @private This is only public so SFSBlacklistUpdate::execute can access it
	 */
	public static function getBlacklistUpdateStateKey() {
		return 'sfs:blacklist:updatestate';
	}

	/**
	 * Checks if a given IP address is blacklisted
	 * @param string $ip
	 * @return bool
	 */
	public static function isBlacklisted( $ip ) {
		global $wgMemc;
		if ( !IPUtils::isValid( $ip ) || IPUtils::isIPv6( $ip ) ) {
			return false;
		}
		list( $bucket, $offset ) = self::getBucketAndOffset( $ip );
		$bitfield = $wgMemc->get( self::getIPBlacklistKey( $bucket ) );

		return (bool)( $bitfield & ( 1 << $offset ) );
	}

	/**
	 * Gets the bucket (cache key) and offset (bit within the cache)
	 * @param string $ip
	 * @return int[]
	 * @private This is only public so SFSBlacklistUpdate::execute can access it
	 */
	public static function getBucketAndOffset( $ip ) {
		if ( self::$SHIFT_AMOUNT === null ) {
			self::$SHIFT_AMOUNT = ( PHP_INT_SIZE == 4 ) ? 5 : 6;
			self::$BUCKET_MASK = ( PHP_INT_SIZE == 4 ) ? 134217727 : 67108863;
			self::$OFFSET_MASK = ( PHP_INT_SIZE == 4 ) ? 31 : 63;
		}
		$ip = ip2long( $ip );
		$bucket = ( $ip >> self::$SHIFT_AMOUNT ) & self::$BUCKET_MASK;
		$offset = $ip & self::$OFFSET_MASK;

		return [ $bucket, $offset ];
	}
}
