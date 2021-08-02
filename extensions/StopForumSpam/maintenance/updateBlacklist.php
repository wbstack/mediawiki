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

use MediaWiki\StopForumSpam\BlacklistUpdate;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Reads the blacklist file and sticks it in memcache
 */
class SFSBlacklistUpdate extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->requireExtension( 'StopForumSpam' );
	}

	public function execute() {
		global $wgSFSIPListLocation;
		if ( $wgSFSIPListLocation === false ) {
			$this->error( '$wgSFSIPListLocation has not been configured properly.' );
		}
		$this->output( "Starting...\n" );
		$before = microtime( true );

		$update = new BlacklistUpdate();
		// Where the magic happens!
		$update->doUpdate();

		$diff = microtime( true ) - $before;

		$this->output( "Done!\n" );
		$this->output( "Took {$diff} seconds\n" );
	}

}

$maintClass = 'SFSBlacklistUpdate';
require_once RUN_MAINTENANCE_IF_MAIN;
