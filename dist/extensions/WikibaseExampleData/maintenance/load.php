<?php

namespace MediaWiki\Extension\WikibaseExampleData\Maintenance;

use MediaWiki\Extension\WikibaseExampleData\DataLoader;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class Load extends \Maintenance {

	public function execute() {
		$loader = new DataLoader();
		$loader->execute();
	}

}

$maintClass = "MediaWiki\Extension\WikibaseExampleData\Maintenance\Load";
require_once RUN_MAINTENANCE_IF_MAIN;
