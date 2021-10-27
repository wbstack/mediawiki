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

use DeferrableUpdate;
use DomainException;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\MediaWikiServices;
use RuntimeException;
use Wikimedia\IPUtils;

class DenyListUpdate implements DeferrableUpdate {

	/**
	 * perform update (get deny list, load into cache)
	 *
	 * @return string[] List of denylisted IP addresses
	 */
	public function doUpdate() : array {
		global $wgSFSIPListLocation;
		if ( $wgSFSIPListLocation === false ) {
			throw new DomainException(
				'$wgSFSIPListLocation has not been configured properly.'
			);
		}
		return self::loadDenyListIPs();
	}

	/**
	 * get array of denylisted IPs from cache
	 *
	 * @return string[] List of denylisted IP addresses
	 */
	public static function getDenyListIPs() {
		$wanCache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		return $wanCache->get(
			$wanCache->makeGlobalKey( DenyListManager::getDenyListKey() )
		);
	}

	/**
	 * purge cache of denylist IPs
	 *
	 * @return bool
	 */
	public static function purgeDenyListIPs() {
		$wanCache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		return $wanCache->delete(
			$wanCache->makeGlobalKey( DenyListManager::getDenyListKey() )
		);
	}

	/**
	 * Update cache with IPs and return them
	 *
	 * @return string[] List of denylisted IP addresses
	 */
	public static function loadDenyListIPs() : array {
		global $wgSFSDenyListCacheDuration;
		$wanCache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		return $wanCache->getWithSetCallback(
			$wanCache->makeGlobalKey( DenyListManager::getDenyListKey() ),
			$wgSFSDenyListCacheDuration,
			function () {
				global $wgSFSIPListLocation;
				$IPs = is_file( $wgSFSIPListLocation )
					? self::fetchDenyListIPsLocal()
					: self::fetchDenyListIPsRemote();
				return $IPs;
			},
			[
				'lockTSE' => $wgSFSDenyListCacheDuration,
				'staleTTL' => $wgSFSDenyListCacheDuration,
				'busyValue' => []
			]
		);
	}

	/**
	 * Fetch gunzipped/unzipped SFS deny list from local file
	 *
	 * @return string[] list of SFS denylisted IP addresses
	 */
	private static function fetchDenyListIPsLocal() : array {
		global $wgSFSIPListLocation,
			$wgSFSValidateIPList,
			$wgSFSIPThreshold;

		if ( !is_file( $wgSFSIPListLocation ) ) {
			throw new DomainException( "wgSFSIPListLocation does not appear to be a valid file path." );
		}

		$ipList = [];
		$fh = fopen( $wgSFSIPListLocation, 'rb' );

		if ( !$fh ) {
			throw new DomainException( "wgSFSIPListLocation file handle could not be obtained." );
		}

		// Set up output buffering so we don't accidentally try to send stuff
		ob_start();
		while ( !feof( $fh ) ) {
			$ip = fgetcsv( $fh, 4096, ',', '"' );
			if ( $ip === false ) {
				break;
			}

			if (
				$ip === null ||
				$ip === [ null ] ||
				// @phan-suppress-next-line PhanTypeMismatchArgumentNullable
				( $wgSFSValidateIPList && IPUtils::sanitizeIP( $ip[0] ) === null )
			) {
				continue;
			} elseif ( isset( $ip[1] ) && $ip[1] < $wgSFSIPThreshold ) {
				continue;
			} else {
				// add to list
				$ipList[] = $ip[0];
			}
		}
		fclose( $fh );
		ob_end_clean();

		return $ipList;
	}

	/**
	 * Fetch a network file's contents via HttpRequestFactory
	 *
	 * @param HttpRequestFactory $factory
	 * @param array $httpOptions
	 * @param string $fileUrl
	 * @return null|string
	 */
	private static function fetchRemoteFile(
		HttpRequestFactory $factory,
		array $httpOptions,
		string $fileUrl
	) {
		$req = $factory->create( $fileUrl, $httpOptions );
		if ( !$req->execute()->isOK() ) {
			throw new RuntimeException( "Failed to download resource at {$fileUrl}" );
		}
		if ( $req->getStatus() !== 200 ) {
			throw new RuntimeException( "Unexpected HTTP {$req->getStatus()} response from {$fileUrl}" );
		}
		return $req->getContent();
	}

	/**
	 * Fetch SFS IP deny list file from SFS site, validate MD5 and returns array of IPs
	 * (https://www.stopforumspam.com/downloads - use gz files)
	 *
	 * @return string[] list of SFS denylisted IP addresses
	 */
	private static function fetchDenyListIPsRemote() : array {
		global $wgSFSIPListLocation, $wgSFSIPListLocationMD5, $wgSFSProxy;

		// Hacky, but neededed to keep a sensible default value of $wgSFSIPListLocation for
		// users, whilst also preventing HTTP requests for other extension when they call
		// permission related hooks that mean the code here gets executed too...
		// So, if we have a URL, and try and do a HTTP request whilst in MW_PHPUNIT_TEST,
		// just fallback to loading sample_denylist_all.txt as a file...
		// See also: T262443, T265628.
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			$wgSFSIPListLocation = dirname( __DIR__ ) . '/tests/phpunit/sample_denylist_all.txt';
			return self::fetchDenyListIPsLocal();
		}

		// check for zlib function for later processing
		if ( !function_exists( 'gzdecode' ) ) {
			throw new RuntimeException( "Zlib does not appear to be configured for php!" );
		}

		if ( !filter_var( $wgSFSIPListLocation, FILTER_VALIDATE_URL ) ) {
			throw new DomainException( "wgSFSIPListLocation does not appear to be a valid URL." );
		}

		// fetch vendor http resources
		$reqFac = MediaWikiServices::getInstance()->getHttpRequestFactory();

		$options = [
			'followRedirects' => true,
		];

		if ( $wgSFSProxy !== false ) {
			$options['proxy'] = $wgSFSProxy;
		}

		$fileData = self::fetchRemoteFile(
			$reqFac,
			$options,
			$wgSFSIPListLocation
		);
		$fileDataMD5 = self::fetchRemoteFile(
			$reqFac,
			$options,
			$wgSFSIPListLocationMD5
		);

		// check vendor-provided md5
		if ( $fileData == null || md5( $fileData ) !== $fileDataMD5 ) {
			throw new RuntimeException( "SFS IP file contents and file md5 do not match!" );
		}

		// ungzip and process vendor file
		$fileDataProcessed = explode( "\n", gzdecode( $fileData ) );
		array_walk( $fileDataProcessed, function ( &$item, $key ) {
			global $wgSFSValidateIPList, $wgSFSIPThreshold;
			$ipData = str_getcsv( $item );

			$ip = (string)$ipData[0];

			if ( $wgSFSValidateIPList
				&& IPUtils::sanitizeIP( $ip ) === null
			) {
				$item = '';
				return;
			}
			$score = (int)$ipData[1];
			if ( $score && ( $score < $wgSFSIPThreshold ) ) {
				$item = '';
				return;
			}
			$item = $ip;
		} );
		return array_filter( $fileDataProcessed );
	}
}
