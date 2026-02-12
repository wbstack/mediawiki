<?php

namespace MediaWiki\Extension\Elastica;

use Elastica\Client;
use Elastica\Index;
use Mediawiki\Http\Telemetry;
use MediaWiki\Logger\LoggerFactory;

/**
 * Forms and caches connection to Elasticsearch as well as client objects
 * that contain connection information like \Elastica\Index. Propagates
 * distributed tracing headers.
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
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */
abstract class ElasticaConnection {
	/**
	 * @var ?Client
	 */
	protected $client;

	/**
	 * @return string[] server ips or hostnames
	 */
	abstract public function getServerList();

	/**
	 * How many times can we attempt to connect per host?
	 *
	 * @return int
	 */
	public function getMaxConnectionAttempts() {
		return 1;
	}

	/**
	 * Set the client side timeout to be used for the rest of this process.
	 * @param int $timeout timeout in seconds
	 */
	public function setTimeout( $timeout ) {
		$client = $this->getClient();
		// Set the timeout for new connections
		$client->setConfigValue( 'timeout', $timeout );
		foreach ( $client->getConnections() as $connection ) {
			$connection->setTimeout( $timeout );
		}
	}

	/**
	 * Set the client side connect timeout to be used for the rest of this process.
	 * @param int $timeout timeout in seconds
	 */
	public function setConnectTimeout( $timeout ) {
		$client = $this->getClient();
		// Set the timeout for new connections
		$client->setConfigValue( 'connectTimeout', $timeout );
		foreach ( $client->getConnections() as $connection ) {
			$connection->setConnectTimeout( $timeout );
		}
	}

	/**
	 * Fetch a connection.
	 * @return Client
	 */
	public function getClient() {
		if ( $this->client === null ) {
			// Setup the Elastica servers
			$servers = [];
			$serverList = $this->getServerList();
			if ( !is_array( $serverList ) ) {
				$serverList = [ $serverList ];
			}
			foreach ( $serverList as $server ) {
				if ( !is_array( $server ) ) {
					$server = [ 'host' => $server ];
				}
				$server['headers'] = ( $server['headers'] ?? [] )
					+ Telemetry::getInstance()->getRequestHeaders();
				$servers[] = $server;
			}

			$this->client = new Client( [ 'servers' => $servers ],
				/**
				 * Callback for \Elastica\Client on request failures.
				 * @param \Elastica\Connection $connection The current connection to elasticasearch
				 * @param \Exception $e Exception to be thrown if we don't do anything
				 * @param \Elastica\Client $client
				 */
				function ( $connection, $e, $client ) {
					// We only want to try to reconnect on http connection errors
					// Beyond that we want to give up fast.  Configuring a single connection
					// through LVS accomplishes this.
					if ( !( $e instanceof \Elastica\Exception\Connection\HttpException ) ) {
						LoggerFactory::getInstance( 'Elastica' )
							->error( 'Unknown connection exception communicating with Elasticsearch: {class_name}',
								[ 'class_name' => get_class( $e ) ] );
						return;
					}
					if ( $e->getError() === CURLE_OPERATION_TIMEOUTED ) {
						// Timeouts shouldn't disable the connection and should always be thrown
						// back to the caller so they can catch it and handle it.  They should
						// never be retried blindly.
						$connection->setEnabled( true );
						throw $e;
					}
					if ( $e->getError() === CURLE_PARTIAL_FILE ) {
						// This means the connection dropped before the full response was read,
						// likely some sort of network problem or elasticsearch shut down
						// mid-response. If the network failed or elasticsearch is gone the
						// retry should fail, but we delegate deciding on retries to the caller.
						LoggerFactory::getInstance( 'Elastica' )
							->error( 'Error communicating with elasticsearch, connection closed' .
								'before full response was read.', [ 'exception' => $e ] );
						$connection->setEnabled( true );
						throw $e;
					}
					if ( $e->getError() !== CURLE_COULDNT_CONNECT ) {
						LoggerFactory::getInstance( 'Elastica' )
							->error( 'Unexpected connection error communicating with Elasticsearch. ' .
								'Curl code: {curl_code}', [ 'curl_code' => $e->getError() ] );
						// If there are different connections we could try leave this connection disabled
						// and let Elastica retry on a different connection.
						if ( $client->hasConnection() ) {
							return;
						}
						// Otherwise this was the last available connection.  Re-enable it but throw
						// so that retries are delegated to the application. This prevents the
						// situation where the calling code knows it can retry but no connections remain.
						$connection->setEnabled( true );
						throw $e;
					}
					// Keep track of the number of times we've hit a host
					static $connectionAttempts = [];
					$host = $connection->getParam( 'host' );
					$connectionAttempts[ $host ] = isset( $connectionAttempts[ $host ] )
						? $connectionAttempts[ $host ] + 1 : 1;

					// Check if we've hit the host the max # of times. If not, try again
					if ( $connectionAttempts[ $host ] < $this->getMaxConnectionAttempts() ) {
						LoggerFactory::getInstance( 'Elastica' )
							->info( "Retrying connection to {elastic_host} after {attempts} attempts",
								[
									'elastic_host' => $host,
									'attempts' => $connectionAttempts[ $host ],
								] );
						$connection->setEnabled( true );
					} elseif ( !$client->hasConnection() ) {
						// Don't disable the last connection, but don't let it auto-retry either.
						$connection->setEnabled( true );
						throw $e;
					}
				}
			);
		}

		return $this->client;
	}

	/**
	 * Fetch the Elastica Index.
	 * @param string $name get the index(es) with this basename
	 * @param string|bool $type type of index (named type or false to get all)
	 * @param mixed $identifier if specified get the named identifier of the index
	 * @return Index
	 */
	public function getIndex( $name, $type = false, $identifier = false ) {
		return $this->getClient()->getIndex( $this->getIndexName( $name, $type, $identifier ) );
	}

	/**
	 * Get the name of the index.
	 * @param string $name get the index(es) with this basename
	 * @param string|bool $type type of index (named type or false to get all)
	 * @param mixed $identifier if specified get the named identifier of the index
	 * @return string name of index for $type and $identifier
	 */
	public function getIndexName( $name, $type = false, $identifier = false ) {
		if ( $type ) {
			$name .= '_' . $type;
		}
		if ( $identifier ) {
			$name .= '_' . $identifier;
		}
		return $name;
	}

	public function destroyClient() {
		$this->client = null;
		ElasticaHttpTransportCloser::destroySingleton();
	}
}

class_alias( ElasticaConnection::class, 'ElasticaConnection' );
