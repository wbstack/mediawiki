<?php

namespace Wikibase\Repo\Store\Sql;

use MediaWiki\MediaWikiServices;
use Onoi\MessageReporter\MessageReporter;
use RuntimeException;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\EntityId\EntityIdComposer;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikibase\Lib\Store\Sql\PropertyInfoTable;
use Wikibase\Repo\PropertyInfoBuilder;

/**
 * Utility class for rebuilding the wb_property_info table.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class PropertyInfoTableBuilder {

	/**
	 * @var PropertyInfoTable
	 */
	private $propertyInfoTable;

	/**
	 * @var PropertyLookup
	 */
	private $propertyLookup;

	/**
	 * @var PropertyInfoBuilder
	 */
	private $propertyInfoBuilder;

	/**
	 * @var EntityIdComposer
	 */
	private $entityIdComposer;

	/**
	 * @var EntityNamespaceLookup
	 */
	private $entityNamespaceLookup;

	/**
	 * @var MessageReporter|null
	 */
	private $reporter = null;

	/**
	 * @var bool
	 */
	private $useTransactions = true;

	/**
	 * Whether all entries should be updated, or only missing entries
	 *
	 * @var bool
	 */
	private $shouldUpdateAllEntities = false;

	/**
	 * The batch size, giving the number of rows to be updated in each database transaction.
	 *
	 * @var int
	 */
	private $batchSize = 100;

	public function __construct(
		PropertyInfoTable $propertyInfoTable,
		PropertyLookup $propertyLookup,
		PropertyInfoBuilder $propertyInfoBuilder,
		EntityIdComposer $entityIdComposer,
		EntityNamespaceLookup $entityNamespaceLookup
	) {
		$this->propertyInfoTable = $propertyInfoTable;
		$this->propertyLookup = $propertyLookup;
		$this->propertyInfoBuilder = $propertyInfoBuilder;
		$this->entityIdComposer = $entityIdComposer;
		$this->entityNamespaceLookup = $entityNamespaceLookup;
	}

	/**
	 * @param bool $all
	 */
	public function setRebuildAll( $all ) {
		$this->shouldUpdateAllEntities = $all;
	}

	/**
	 * @param int $batchSize
	 */
	public function setBatchSize( $batchSize ) {
		$this->batchSize = $batchSize;
	}

	/**
	 * Sets the reporter to use for reporting progress.
	 *
	 * @param MessageReporter $reporter
	 */
	public function setReporter( MessageReporter $reporter ) {
		$this->reporter = $reporter;
	}

	/**
	 * Enables or disables transactions.
	 * The only good reason to disable transactions is to be able to
	 * run the rebuild inside an already existing transaction.
	 *
	 * @param bool $useTransactions
	 */
	public function setUseTransactions( $useTransactions ) {
		$this->useTransactions = $useTransactions;
	}

	/**
	 * Rebuild the property info entries.
	 * Use the rebuildPropertyInfo.php maintenance script to invoke this from the command line.
	 *
	 * Database updates a batched into multiple transactions. Do not call this
	 * method within an (explicit) database transaction.
	 */
	public function rebuildPropertyInfo() {
		$propertyNamespace = $this->entityNamespaceLookup->getEntityNamespace(
			Property::ENTITY_TYPE
		);
		if ( $propertyNamespace === null ) {
			throw new RuntimeException( __METHOD__ . ' can not run with no Property namespace defined.' );
		}

		$dbw = $this->propertyInfoTable->getWriteConnection();

		$total = 0;

		$join = [];
		$tables = [ 'page' ];

		if ( !$this->shouldUpdateAllEntities ) {
			$piTable = $this->propertyInfoTable->getTableName();

			$tables[] = $piTable;
			$join[$piTable] = [
				'LEFT JOIN',
				[
					$dbw->buildConcat( [ "'P'", 'pi_property_id' ] ) . ' = page_title',
				]
			];
		}

		// @TODO: Inject the LBFactory
		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		$ticket = $lbFactory->getEmptyTransactionTicket( __METHOD__ );
		$pageId = 1;

		while ( true ) {
			// Make sure we are not running too far ahead of the replicas,
			// as that would cause the site to be rendered read only.
			$lbFactory->commitAndWaitForReplication( __METHOD__, $ticket );

			if ( $this->useTransactions ) {
				$dbw->startAtomic( __METHOD__ );
			}

			$props = $dbw->select(
				$tables,
				[ 'page_title', 'page_id' ],
				[
					'page_id > ' . $pageId,
					'page_namespace = ' . $propertyNamespace,
					$this->shouldUpdateAllEntities ? '1=1' : 'pi_property_id IS NULL', // if not $all, only add missing entries
				],
				__METHOD__,
				[
					'LIMIT' => $this->batchSize,
					'ORDER BY' => 'page_id ASC',
					'FOR UPDATE'
				],
				$join
			);

			$c = 0;

			foreach ( $props as $row ) {
				$this->updatePropertyInfo( new PropertyId( $row->page_title ) );
				$pageId = (int)$row->page_id;
				$c++;
			}

			if ( $this->useTransactions ) {
				$dbw->endAtomic( __METHOD__ );
			}

			$this->reportMessage( "Updated $c properties, up to page ID $pageId." );
			$total += $c;

			if ( $c < $this->batchSize ) {
				break;
			}
		}

		return $total;
	}

	/**
	 * Updates the property info entry for the given property.
	 * The property is loaded in full using the EntityLookup
	 * provide to the constructor.
	 *
	 * @throws RuntimeException
	 *
	 * @param PropertyId $id the Property to process
	 */
	private function updatePropertyInfo( PropertyId $id ) {
		$property = $this->propertyLookup->getPropertyForId( $id );

		if ( $property === null ) {
			throw new RuntimeException(
				'Did not find Property with id ' . $id->getSerialization()
			);
		}

		$info = $this->propertyInfoBuilder->buildPropertyInfo( $property );

		$this->propertyInfoTable->setPropertyInfo(
			$property->getId(),
			$info
		);
	}

	/**
	 * @param string $msg
	 */
	private function reportMessage( $msg ) {
		if ( $this->reporter ) {
			$this->reporter->reportMessage( $msg );
		}
	}

}
