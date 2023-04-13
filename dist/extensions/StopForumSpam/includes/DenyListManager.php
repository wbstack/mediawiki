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

use BagOStuff;
use DomainException;
use IStoreKeyEncoder;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use WANObjectCache;
use Wikimedia\IPSet;
use Wikimedia\IPUtils;

/**
 * @internal
 */
class DenyListManager {

	private const CACHE_VERSION = 1;

	/** @var HttpRequestFactory */
	private $http;
	/** @var BagOStuff */
	private $srvCache;
	/** @var WANObjectCache */
	private $wanCache;
	/** @var LoggerInterface */
	private $logger;

	/** @var IPSet|null */
	private $denyListIPSet;

	/** @var self */
	private static $instance = null;

	/**
	 * @param HttpRequestFactory $http
	 * @param BagOStuff $srvCache
	 * @param WANObjectCache $wanCache
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(
		HttpRequestFactory $http,
		BagOStuff $srvCache,
		WANObjectCache $wanCache,
		?LoggerInterface $logger
	) {
		$this->http = $http;
		$this->srvCache = $srvCache;
		$this->wanCache = $wanCache;
		$this->logger = $logger ?: new NullLogger();
	}

	/**
	 * @todo use MediaWikiServices
	 * @return DenyListManager
	 */
	public static function singleton() {
		if ( self::$instance == null ) {
			$services = MediaWikiServices::getInstance();

			$srvCache = $services->getLocalServerObjectCache();
			$wanCache = $services->getMainWANObjectCache();
			$http = $services->getHttpRequestFactory();
			$logger = LoggerFactory::getInstance( 'DenyList' );

			self::$instance = new self( $http, $srvCache, $wanCache, $logger );
		}

		return self::$instance;
	}

	/**
	 * Check whether the IP address is deny-listed
	 *
	 * @param string $ip An IP address
	 * @return bool
	 */
	public function isIpDenyListed( $ip ) {
		if ( IPUtils::isIPAddress( $ip ) === null ) {
			return false;
		}

		return $this->getIpDenyListSet()->match( $ip );
	}

	/**
	 * Get the list of deny-listed IPs from cache only
	 *
	 * @return string[]|false List of deny-listed IP addresses; false if uncached
	 */
	public function getCachedIpDenyList() {
		return $this->getIpDenyList();
	}

	/**
	 * Purge cache of deny-list IPs
	 *
	 * @return bool Success
	 */
	public function purgeCachedIpDenyList() {
		$wanCache = $this->wanCache;

		return $wanCache->delete( $this->getDenyListKey( $wanCache ) );
	}

	/**
	 * Fetch the list of IPs from cache, regenerating the cache as needed
	 *
	 * @param string|null $recache Use 'recache' to force a recache
	 * @return string[] List of deny-listed IP addresses
	 */
	public function getIpDenyList( $recache = null ): array {
		global $wgSFSDenyListCacheDuration;

		$srvCache = $this->srvCache;
		$srvCacheKey = $this->getDenyListKey( $srvCache );
		if ( $recache === 'recache' ) {
			$flatIpList = false;
		} else {
			$flatIpList = $srvCache->get( $srvCacheKey );
		}

		if ( $flatIpList === false ) {
			$wanCache = $this->wanCache;
			$flatHexIpList = $wanCache->getWithSetCallback(
				$this->getDenyListKey( $wanCache ),
				$wgSFSDenyListCacheDuration,
				function () {
					// This uses hexadecimal IP addresses to reduce network I/O
					return $this->fetchFlatDenyListHexIps();
				},
				[
					'lockTSE' => $wgSFSDenyListCacheDuration,
					'staleTTL' => $wgSFSDenyListCacheDuration,
					// placeholder
					'busyValue' => '',
					'minAsOf' => ( $recache === 'recache' ) ? INF : $wanCache::MIN_TIMESTAMP_NONE
				]
			);

			$ips = [];
			for ( $hex = strtok( $flatHexIpList, "\n" ); $hex !== false; $hex = strtok( "\n" ) ) {
				$ips[] = IPUtils::formatHex( $hex );
			}

			$flatIpList = implode( "\n", $ips );

			// Refill the local server cache if the list is not empty nor a placeholder
			if ( $flatIpList !== '' ) {
				$srvCache->set(
					$srvCacheKey,
					$flatIpList,
					mt_rand( $srvCache::TTL_HOUR, $srvCache::TTL_DAY )
				);
			}
		}

		return ( $flatIpList != '' ) ? explode( "\n", $flatIpList ) : [];
	}

	/**
	 * @param string|null $recache Use 'recache' to force a recache
	 * @return IPSet
	 */
	public function getIpDenyListSet( $recache = null ) {
		if ( $this->denyListIPSet === null || $recache === "recache" ) {
			$this->denyListIPSet = new IPSet( $this->getIpDenyList( $recache ) );
		}

		return $this->denyListIPSet;
	}

	/**
	 * @param IStoreKeyEncoder $cache
	 * @return string Cache key for primary deny list
	 */
	private function getDenyListKey( IStoreKeyEncoder $cache ) {
		return $cache->makeGlobalKey( 'sfs-denylist-set', self::CACHE_VERSION );
	}

	/**
	 * @return string Newline separated list of SFS deny-listed IP addresses
	 */
	private function fetchFlatDenyListHexIps(): string {
		global $wgSFSIPListLocation, $wgSFSValidateIPListLocationMD5;

		if ( $wgSFSIPListLocation === false ) {
			throw new DomainException( '$wgSFSIPListLocation has not been configured.' );
		}

		if ( is_file( $wgSFSIPListLocation ) ) {
			$ipList = $this->fetchFlatDenyListHexIpsLocal( $wgSFSIPListLocation );
		} else {
			$ipList = $this->fetchFlatDenyListHexIpsRemote(
				$wgSFSIPListLocation,
				$wgSFSValidateIPListLocationMD5
			);
		}

		return $ipList;
	}

	/**
	 * Fetch gunzipped/unzipped SFS deny list from local file
	 *
	 * @param string $listFilePath Local file path
	 * @return string Newline separated list of SFS deny-listed IP addresses
	 */
	private function fetchFlatDenyListHexIpsLocal( string $listFilePath ): string {
		global $wgSFSIPThreshold;

		$fh = fopen( $listFilePath, 'rb' );
		if ( !$fh ) {
			throw new DomainException( "wgSFSIPListLocation file handle could not be obtained." );
		}

		$ipList = [];

		while ( !feof( $fh ) ) {
			$ipData = fgetcsv( $fh, 4096, ',', '"' );
			if ( $ipData === false ) {
				break;
			}

			if ( $ipData === null || $ipData === [ null ] ) {
				continue;
			}
			if ( isset( $ipData[1] ) && $ipData[1] < $wgSFSIPThreshold ) {
				continue;
			}

			$ip = (string)$ipData[0];
			$hex = IPUtils::toHex( $ip );
			if ( $hex === false ) {
				// invalid address
				continue;
			}

			$ipList[] = $hex;
		}

		return implode( "\n", $ipList );
	}

	/**
	 * Fetch SFS IP deny list file from SFS site and returns an array of IPs
	 * (https://www.stopforumspam.com/downloads - use gz files)
	 *
	 * @param string $uri SFS vendor or third-party URL to the list
	 * @param string|null $md5uri SFS vendor URL to the MD5 of the list
	 * @return string Newline-separated list of SFS deny-listed IP addresses
	 */
	private function fetchFlatDenyListHexIpsRemote( string $uri, ?string $md5uri ): string {
		global $wgSFSProxy, $wgSFSIPThreshold;

		// Hacky, but needed to keep a sensible default value of $wgSFSIPListLocation for
		// users, whilst also preventing HTTP requests for other extension when they call
		// permission related hooks that mean the code here gets executed too...
		// So, if we have a URL, and try and do a HTTP request whilst in MW_PHPUNIT_TEST,
		// just fallback to loading sample_denylist_all.txt as a file...
		// See also: T262443, T265628.
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			$filePath = dirname( __DIR__ ) . '/tests/phpunit/sample_denylist_all.txt';
			return $this->fetchFlatDenyListHexIpsLocal( $filePath );
		}

		if ( !filter_var( $uri, FILTER_VALIDATE_URL ) ) {
			throw new DomainException( "wgSFSIPListLocation does not appear to be a valid URL." );
		}

		// check for zlib function for later processing
		if ( !function_exists( 'gzdecode' ) ) {
			throw new RuntimeException( "Zlib does not appear to be configured for php!" );
		}

		$options = [ 'followRedirects' => true ];
		if ( $wgSFSProxy !== false ) {
			$options['proxy'] = $wgSFSProxy;
		}

		$fileData = $this->fetchRemoteFile( $uri, $options );
		if ( $fileData === '' ) {
			$this->logger->error( __METHOD__ . ": SFS IP list could not be fetched!" );

			return '';
		}

		if ( is_string( $md5uri ) && $md5uri !== '' ) {
			// check vendor-provided md5
			$fileDataMD5 = $this->fetchRemoteFile( $md5uri, $options );
			if ( $fileDataMD5 === '' ) {
				$this->logger->error( __METHOD__ . ": SFS IP list MD5 could not be fetched!" );
				return '';
			}

			if ( md5( $fileData ) !== $fileDataMD5 ) {
				$this->logger->error( __METHOD__ . ": SFS IP list has an unexpected MD5!" );
				return '';
			}
		}

		// ungzip and process vendor file
		$csvTable = gzdecode( $fileData );
		if ( $csvTable === false ) {
			$this->logger->error( __METHOD__ . ": SFS IP file contents could not be decoded!" );
			return '';
		}

		$ipList = [];
		$scoreSkipped = 0;
		$rows = 0;

		for ( $line = strtok( $csvTable, "\n" ); $line !== false; $line = strtok( "\n" ) ) {

			$rows++;

			$ipData = str_getcsv( $line );
			$ip = (string)$ipData[0];
			$score = (int)$ipData[1];

			if ( $score && ( $score < $wgSFSIPThreshold ) ) {
				$scoreSkipped++;
				continue;
			}

			$hex = IPUtils::toHex( $ip );
			if ( $hex === false ) {
				// invalid address
				continue;
			}

			$ipList[] = $hex;
		}

		if ( $scoreSkipped > 0 ) {
			$this->logger->info(
				__METHOD__ . ": {$rows} rows were processed. "
				. "{$scoreSkipped} were skipped because their score was less than {$wgSFSIPThreshold}."
			);
		}

		return implode( "\n", $ipList );
	}

	/**
	 * Fetch a network file's contents via HttpRequestFactory
	 *
	 * @param string $fileUrl
	 * @param array $httpOptions
	 * @return string
	 */
	private function fetchRemoteFile( string $fileUrl, array $httpOptions ): string {
		$req = $this->http->create( $fileUrl, $httpOptions );

		$status = $req->execute();
		if ( !$status->isOK() ) {
			throw new RuntimeException( "Failed to download resource at {$fileUrl}" );
		}

		$code = $req->getStatus();
		if ( $code !== 200 ) {
			throw new RuntimeException( "Unexpected HTTP {$code} response from {$fileUrl}" );
		}

		return (string)$req->getContent();
	}
}
