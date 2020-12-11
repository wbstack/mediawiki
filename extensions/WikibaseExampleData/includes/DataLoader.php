<?php

namespace MediaWiki\Extension\WikibaseExampleData;

use Wikibase\Repo\WikibaseRepo;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\Item;
use User;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Statement\StatementListProvider;
use Wikibase\DataModel\Statement\StatementListHolder;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\Snak;

class DataLoader {

	private $dataDir = __DIR__ . '/../data/';

	// Of OLD => NEW id
	private $idMap = [];

	public function execute() {
		// Mapping in all arrays is the original ID
		$repo = WikibaseRepo::getDefaultInstance();
		$deserailizer = $repo->getBaseDataModelDeserializerFactory()->newEntityDeserializer();
		$files = $this->getAllFiles();

		$rawEntitiesToImport = [];
		foreach( $files as $file ) {
			$rawJson = file_get_contents( $file );
			$entity = $deserailizer->deserialize( json_decode( $rawJson, true ) );
			$rawEntitiesToImport[$entity->getId()->getSerialization()] = $entity;
		}

		// Get a first round of entities, ONLY with fingerprints, and with IDs removed...
		$roundOneEntitiesToLoad = $this->getFingerprintOnlyEntities( $rawEntitiesToImport );

		// Write the entities
		foreach( $roundOneEntitiesToLoad as $sourceEntityId => $entity ) {
			$savedEntityRevision = $repo->getStore()->getEntityStore()->saveEntity(
				$entity,
				'Import base entity',
				User::newSystemUser( 'WikibaseExampleDataImporter' ),
				EDIT_NEW
			);
			$newIdString = $savedEntityRevision->getEntity()->getId()->getSerialization();
			$this->idMap[ $sourceEntityId ] = $newIdString;
		}
		unset( $roundOneEntitiesToLoad );

		// Get a new set of entity objects with adjusted IDs, including statements
		$roundTwoEntitiesToLoad = $this->adjustIdsInEntities( $rawEntitiesToImport );

		// Write the entities again (this time with statements)
		foreach( $roundTwoEntitiesToLoad as $sourceEntityId => $entity ) {
			if( count( $entity->getStatements()->toArray() ) === 0 ) {
				// Skip entities that would have no statements added
				continue;
			}
			$savedEntityRevision = $repo->getStore()->getEntityStore()->saveEntity(
				$entity,
				'Import statements',
				User::newSystemUser( 'WikibaseExampleDataImporter' )
			);
			$newIdString = $savedEntityRevision->getEntity()->getId()->getSerialization();
			$this->idMap[ $sourceEntityId ] = $newIdString;
		}

	}

	private function getFingerprintOnlyEntities( array $entities ) : array {
		$smallerEntities = [];

		foreach( $entities as $entity ){
			if( $entity->getType() === 'item' ) {
				$smallerEntities[ $entity->getId()->getSerialization() ] = new Item(
					null,
					$entity->getFingerprint()
				);
			} elseif( $entity->getType() === 'property' ) {
				$smallerEntities[ $entity->getId()->getSerialization() ] = new Property(
					null,
					$entity->getFingerprint(),
					$entity->getDataTypeId()
				);
			} else {
				die('ohnoes');
			}

		}

		return $smallerEntities;
	}

	private function adjustIdsInEntities( array $entities ) : array {
		/** @var StatementListProvider $entity */
		foreach( $entities as $sourceEntityId => $entity ) {
			// Adjust main IDs
			if( $entity->getType() === 'item' ) {
				$entity->setId( new ItemId( $this->idMap[$sourceEntityId] ) );
			} elseif( $entity->getType() === 'property' ) {
				$entity->setId( new PropertyId( $this->idMap[$sourceEntityId] ) );
			} else {
				die('ohnoes2');
			}
			// Adjust Statement MainSnakValues
			if( $entity instanceof StatementListProvider && $entity instanceof StatementListHolder ){
				$newStatements = new StatementList();
				foreach( $entity->getStatements()->toArray() as $statement ) {
					$newStatements->addStatement(
						new Statement(
							$this->getAdjustedMainsnak( $statement->getMainSnak() )
						)
					);
				}
				$entity->setStatements( $newStatements );
			}
		}
		return $entities;
	}

	private function getAdjustedMainsnak( Snak $mainSnak ) {
		// It would be nice is this sort of thing was in data model?
		$propertyIdToUse = new PropertyId( $this->idMap[$mainSnak->getPropertyId()->getSerialization()] );
		if( $mainSnak instanceof PropertyValueSnak ) {
			return new PropertyValueSnak(
				$propertyIdToUse,
				$mainSnak->getDataValue()
			);
		} elseif( $mainSnak instanceof PropertySomeValueSnak ) {
			return new PropertySomeValueSnak( $propertyIdToUse );

		} elseif ( $mainSnak instanceof PropertyNoValueSnak ){
			return new PropertyNoValueSnak( $propertyIdToUse );
		}
		die('ohnoes3');
	}

	private function getAllFiles() {
		return glob( $this->dataDir . '*' );
	}

}
