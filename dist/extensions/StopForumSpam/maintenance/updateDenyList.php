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

use Maintenance;
use Wikimedia\IPUtils;

require_once getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . "/maintenance/Maintenance.php"
	: __DIR__ . '/../../../maintenance/Maintenance.php';

/**
 * Reads the deny-list file and sticks it in the wancache
 *
 * @codeCoverageIgnore
 */
class UpdateDenyList extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Load the list of StopForumSpam deny-listed ' .
			'IPs when no additional arguments are passed.' );
		$this->addOption(
			'show',
			'Print the current list of deny-listed IPs'
		);
		$this->addOption(
			'purge',
			'Delete the current list of deny-listed IPs from cache'
		);
		$this->addOption(
			'check-ip',
			'Check if a specific IP is within the cache',
			false,
			true
		);
		$this->requireExtension( 'StopForumSpam' );
	}

	public function execute() {
		global $wgSFSIPListLocation;

		if ( $wgSFSIPListLocation === false ) {
			$this->fatalError( '$wgSFSIPListLocation has not been configured.' );
		}

		if ( $this->hasOption( 'show' ) ) {
			$this->output( "List of SFS IPs...\n\n" );
			$ipAddresses = DenyListManager::singleton()->getCachedIpDenyList();
			$this->output(
				is_array( $ipAddresses )
				? implode( "\n", $ipAddresses ) . "\n"
				: "No deny-listed IPs found in cache.\n"
			);
			return;
		}

		if ( $this->hasOption( 'purge' ) ) {
			$this->output( "Purge list of SFS IPs from cache...\n" );
			$this->output(
				DenyListManager::singleton()->purgeCachedIpDenyList() === true ?
				"Purge successful\n" : "Purge failed.\n"
			);
			return;
		}

		if ( $this->hasOption( 'check-ip' ) ) {
			$ipAddress = IPUtils::sanitizeIP( $this->getOption( 'check-ip' ) );
			if ( $ipAddress !== null ) {
				$this->output( "Checking cache for IP '{$ipAddress}'...\n\n" );
				$result = DenyListManager::singleton()->isIpDenyListed( $ipAddress );
				$outputMsg = $result == true ? "Found!" : "NOT Found!";
				$this->output( "{$outputMsg}\n\n" );
			} else {
				$this->output( "Invalid IP address provided." );
			}
			return;
		}

		$this->output( "Starting update of SFS deny-list in cache...\n" );
		$before = microtime( true );

		// Where the magic happens!
		$results = DenyListManager::singleton()->getIpDenyList( 'recache' );
		if ( $results ) {
			$diff = microtime( true ) - $before;

			$numIPs = count( $results );
			$this->output( "Done! Loaded {$numIPs} IPs.\n" );
			$this->output( "Took {$diff} seconds\n" );
		} else {
			$this->fatalError( "Failed!\n" );
		}
	}

}

$maintClass = UpdateDenyList::class;
require_once RUN_MAINTENANCE_IF_MAIN;
