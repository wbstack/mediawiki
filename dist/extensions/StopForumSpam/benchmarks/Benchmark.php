<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace MediaWiki\StopForumSpam;

use Benchmarker;
use const RUN_MAINTENANCE_IF_MAIN;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/includes/Benchmarker.php";

/**
 * Benchmark the StopForumSpam data set loading into an IPSet
 *
 * @codeCoverageIgnore
 */
class Benchmark extends Benchmarker {
	public function __construct() {
		$this->defaultCount = 10;
		parent::__construct();
		$this->addDescription( 'Benchmark for loading SFS into IPSet' );
		$this->requireExtension( 'StopForumSpam' );
	}

	public function execute() {
		$manager = DenyListManager::singleton();
		$benches = [
			// Fresh IPSet, loading from the internet
			[
				'function' => [ $manager, 'getIpDenyListSet' ],
				'args' => [ 'recache' ],
			],

			// Loading IPSet of SFS data from cache
			[
				'function' => [ $manager, 'getIpDenyListSet' ],
			],

			// Testing if IP is in SFS dataset; uses 127.0.0.1 as that shouldn't be listed...
			[
				'function' => [ $manager, 'isIpDenyListed' ],
				'args' => [ '127.0.0.1' ],
			]
		];
		$this->bench( $benches );
	}
}

$maintClass = Benchmark::class;
require_once RUN_MAINTENANCE_IF_MAIN;
