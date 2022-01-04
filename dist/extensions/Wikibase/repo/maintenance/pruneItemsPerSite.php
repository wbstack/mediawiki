<?php

declare( strict_types = 1 );
namespace Wikibase\Repo\Maintenance;

use Maintenance;
use Wikibase\DataModel\Entity\Item;
use Wikibase\Lib\Rdbms\RepoDomainDb;
use Wikibase\Lib\WikibaseSettings;
use Wikibase\Repo\WikibaseRepo;
use Wikimedia\Rdbms\IDatabase;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false ? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../../..';

require_once $basePath . '/maintenance/Maintenance.php';

/**
 * Maintenance script for pruning rows belonging to deleted or redirected items
 * from the wb_items_per_site table.
 *
 * @license GPL-2.0-or-later
 * @author Marius Hoch < hoo@online.de >
 */
class PruneItemsPerSite extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Pune rows belonging to deleted or redirected Items from the wb_items_per_site table' );

		$this->addOption( 'select-batch-size', "Number of table rows to scan per select (100000 by default)", false, true );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		if ( !WikibaseSettings::isRepoEnabled() ) {
			$this->fatalError( "You need to have Wikibase enabled in order to use this maintenance script!\n\n" );
		}

		if ( !in_array( Item::ENTITY_TYPE, WikibaseRepo::getLocalEntitySource()->getEntityTypes() ) ) {
			$this->fatalError(
				"This script assumes Items to be part of the local entity source."
			);
		}

		$itemNamespace = WikibaseRepo::getEntityNamespaceLookup()->getEntityNamespace( Item::ENTITY_TYPE );

		$db = WikibaseRepo::getRepoDomainDbFactory()->newRepoDb();
		$selectBatchSize = (int)$this->getOption( 'select-batch-size', 100000 );

		$this->prune( $db, $itemNamespace, $selectBatchSize );
	}

	private function prune(
		RepoDomainDb $db,
		int $itemNamespace,
		int $selectBatchSize
	) {
		$dbr = $db->connections()->getReadConnectionRef( [ 'vslow' ] );
		$dbw = $db->connections()->getWriteConnectionRef();

		$maxIpsRowId = (int)$dbr->selectField( 'wb_items_per_site', 'MAX(ips_row_id)', '', __METHOD__ );
		// Add 1%, but at least 50, to the maxIpsRowId to use, for items created during the script run
		$maxIpsRowId = max( $maxIpsRowId * 1.01, $maxIpsRowId + 50 );

		$startRowId = (int)$dbr->selectField( 'wb_items_per_site', 'MIN(ips_row_id)', '', __METHOD__ );
		while ( $startRowId < $maxIpsRowId ) {
			$endRowId = $startRowId + $selectBatchSize;
			$rowsToDelete = $this->selectInRange( $dbr, $itemNamespace, $startRowId, $endRowId );
			$this->output( "Read up to ips_row_id $endRowId.\n" );

			if ( $rowsToDelete ) {
				$affectedRows = $this->deleteRows( $dbw, $rowsToDelete );
				$this->output( "Deleted $affectedRows rows.\n" );
				$db->replication()->wait();
			}

			$startRowId = $endRowId;
		}
	}

	private function selectInRange( IDatabase $dbr, int $itemNamespace, int $startRowId, int $endRowId ): array {
		return $dbr->selectFieldValues(
			[ 'wb_items_per_site', 'page' ],
			'ips_row_id',
			[
				'ips_row_id >= ' . $startRowId,
				'ips_row_id < ' . $endRowId,
				'page_id IS NULL',
			],
			__METHOD__,
			[],
			[
				'page' => [
					'LEFT JOIN',
					[
						'page_title = ' . $dbr->buildConcat( [
							$dbr->addQuotes( "Q" ),
							"ips_item_id",
						] ),
						'page_namespace' => $itemNamespace,
						'page_is_redirect' => 0,
					],
				],
			]
		);
	}

	private function deleteRows( IDatabase $dbw, array $rowsToDelete ): int {
		$dbw->delete(
			'wb_items_per_site',
			[
				'ips_row_id' => $rowsToDelete
			],
			__METHOD__
		);

		return $dbw->affectedRows();
	}

}

$maintClass = PruneItemsPerSite::class;
require_once RUN_MAINTENANCE_IF_MAIN;
