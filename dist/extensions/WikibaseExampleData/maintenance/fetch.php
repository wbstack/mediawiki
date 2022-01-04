<?php

namespace MediaWiki\Extension\WikibaseExampleData\Maintenance;

use Wikimedia\Api\WikimediaFactory;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Entity\EntityIdValue;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

// This script is only meant for use at build time, so load vendor/autoload that has dev deps
// FIXME, maybe this shouldnt be a maint script at all...
require_once __DIR__ . '/../vendor/autoload.php';

class Fetch extends \Maintenance {
	/**
	 * @var \Wikibase\Api\WikibaseFactory
	 */
	private $wbFactory;
	private $dataDir = __DIR__ . '/../data/';

	private $propertyIdStrrings = [
		'P31', // instance of >>>>>
		'P50', // author >>>>>
		'P123', // publisher >>>>>>>
		'P577', // publication date
		'P1476', // title
		'P5331', // OCLC id?
	];

	private $itemIdStrings = [
		'Q40205', // Q40205, Cloud Atlas
		'Q374204', // Q374204 , Life of Pi
		'Q1199348', // Q1199348, The Long Dark Tea-Time of the Soul
	];

	public function __construct() {
		parent::__construct();
		$this->addOptions();
	}

	private function addOptions() {
		//$this->addOption( 'file', 'File with list of entity ids to import', false, true );
	}

	private function setupServices() {
		$factory = new WikimediaFactory();
		$this->wbFactory  = $factory->newWikibaseFactoryForDomain( 'wikidata.org' );
	}

	public function execute() {
		$this->setupServices();
		$this->sync();
	}

	public function sync() {
		$properties = $this->getProperties();
		$items = $this->getItems( $this->itemIdStrings, $this->propertyIdStrrings );

		$propertyIdsThatAreItems = $this->getPropertyIdsThatAreItem( $properties );
		$secondaryItemIdsStringsToFetch = $this->findItemsNeededForMainSnaksOnItems( $items, $propertyIdsThatAreItems );
		// Don't refetch items that we have already found as a primary item
		$secondaryItemIdsStringsToFetch = array_diff( $secondaryItemIdsStringsToFetch, $this->itemIdStrings );
		$secondaryItems = $this->getItems( $secondaryItemIdsStringsToFetch, [] );

		$this->writeEntities( $properties );
		$this->writeEntities( $items );
		$this->writeEntities( $secondaryItems );
	}

	private function getPropertyIdsThatAreItem( array $properties ) {
		$filtered = [];
		foreach( $properties as $property ) {
			if( $property->getDataTypeId() === 'wikibase-item' ) {
				$filtered[] = $property->getId();
			}
		}
		return $filtered;
	}

	private function getProperties(){
		$propertyLookup = $this->wbFactory->newPropertyLookup();
		$properties = [];
		foreach( $this->propertyIdStrrings as $propertyIdToImport ) {
			$property = $propertyLookup->getPropertyForId( new PropertyId( $propertyIdToImport ) );
			$properties[] = $this->slimPropertyCopy( $property );
		}
		return $properties;
	}

	private function getItems( array $itemIdStrings, array $propertyStringsToKeep ){
		$itemLookup = $this->wbFactory->newItemLookup();
		$items = [];
		foreach( $itemIdStrings as $itemIdToImport ) {
			$item = $itemLookup->getItemForId( new ItemId( $itemIdToImport ) );
			$items[] = $this->slimItemCopy( $item, $propertyStringsToKeep );
		}
		return $items;
	}

	/**
	 * @param Item[] $items
	 * @param PropertyId[] $propertyIds
	 * @return string[]
	 */
	private function findItemsNeededForMainSnaksOnItems( array $items, array $propertyIds ) {
		$itemIdStrings = [];
		foreach ( $items as $item ) {
			foreach( $propertyIds as $propertyId ) {
				foreach( $item->getStatements()->getByPropertyId( $propertyId ) as $statement ) {
					$mainSnak = $statement->getMainSnak();
					if( $mainSnak->getType() === 'value' ) {
						/** @var EntityIdValue $value */
						$value = $mainSnak->getDataValue();
						$itemIdStrings[] = $value->getEntityId()->serialize();
					}
				}
			}
		}
		return $itemIdStrings;
	}

	private function writeEntities( array $entities ) {
		foreach( $entities as $entity ) {
			file_put_contents(
				$this->dataDir . '/' . $entity->getId()->serialize() . '.json',
				json_encode(
					$this->wbFactory->newDataModelSerializerFactory()->newEntitySerializer()->serialize( $entity ),
					JSON_PRETTY_PRINT
					)
			);
		}
	}

	private function slimPropertyCopy( Property $property ){
		return new Property(
			$property->getId(),
			$property->getFingerprint(),
			$property->getDataTypeId()
		);
	}

	private function slimItemCopy( Item $item, array $propertyStringsToKeep ){
		$slim = new Item(
			$item->getId(),
			$item->getFingerprint()
		);
		foreach( $propertyStringsToKeep as $propertyStringToKeep ) {
			foreach( $item->getStatements()->getByPropertyId( new PropertyId( $propertyStringToKeep ) ) as $statement ) {
				$slim->getStatements()->addStatement( $this->copyWithoutRefsAndQualifiers( $statement ) );
			}
		}
		return $slim;
	}

	private function copyWithoutRefsAndQualifiers( Statement $statement ){
		return new Statement( $statement->getMainSnak() );
	}

}

$maintClass = "MediaWiki\Extension\WikibaseExampleData\Maintenance\Fetch";
require_once RUN_MAINTENANCE_IF_MAIN;
