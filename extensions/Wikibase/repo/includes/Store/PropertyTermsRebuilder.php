<?php

namespace Wikibase\Repo\Store;

use Exception;
use Onoi\MessageReporter\MessageReporter;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Services\EntityId\SeekableEntityIdPager;
use Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\DataModel\Services\Term\PropertyTermStoreWriter;
use Wikimedia\Rdbms\ILBFactory;

/**
 * @license GPL-2.0-or-later
 */
class PropertyTermsRebuilder {

	/** @var PropertyTermStoreWriter */
	private $propertyTermStoreWriter;
	/** @var SeekableEntityIdPager */
	private $idPager;
	/** @var MessageReporter */
	private $progressReporter;
	/** @var MessageReporter */
	private $errorReporter;
	/** @var ILBFactory */
	private $loadBalancerFactory;
	/** @var PropertyLookup */
	private $propertyLookup;
	/** @var int */
	private $batchSize;
	/** @var int */
	private $batchSpacingInSeconds;

	/**
	 * @param PropertyTermStoreWriter $propertyTermStoreWriter
	 * @param SeekableEntityIdPager $idPager
	 * @param MessageReporter $progressReporter
	 * @param MessageReporter $errorReporter
	 * @param ILBFactory $loadBalancerFactory
	 * @param PropertyLookup $propertyLookup
	 * @param int $batchSize
	 * @param int $batchSpacingInSeconds
	 */
	public function __construct(
		PropertyTermStoreWriter $propertyTermStoreWriter,
		SeekableEntityIdPager $idPager,
		MessageReporter $progressReporter,
		MessageReporter $errorReporter,
		ILBFactory $loadBalancerFactory,
		PropertyLookup $propertyLookup,
		$batchSize,
		$batchSpacingInSeconds
	) {
		$this->propertyTermStoreWriter = $propertyTermStoreWriter;
		$this->idPager = $idPager;
		$this->progressReporter = $progressReporter;
		$this->errorReporter = $errorReporter;
		$this->loadBalancerFactory = $loadBalancerFactory;
		$this->propertyLookup = $propertyLookup;
		$this->batchSize = $batchSize;
		$this->batchSpacingInSeconds = $batchSpacingInSeconds;
	}

	public function rebuild() {
		$ticket = $this->loadBalancerFactory->getEmptyTransactionTicket( __METHOD__ );

		while ( true ) {
			$propertyIds = $this->idPager->fetchIds( $this->batchSize );

			if ( !$propertyIds ) {
				break;
			}

			$this->rebuildTermsForBatch( $propertyIds );

			$success = $this->loadBalancerFactory->commitAndWaitForReplication( __METHOD__, $ticket );

			$this->progressReporter->reportMessage(
				'Processed up to page '
				. $this->idPager->getPosition() . ' (' . end( $propertyIds ) . ')'
			);

			if ( !$success ) {
				$this->errorReporter->reportMessage(
					'commitAndWaitForReplication() timed out, aborting'
				);
				break;
			}

			if ( $this->batchSpacingInSeconds > 0 ) {
				sleep( $this->batchSpacingInSeconds );
			}
		}
	}

	private function rebuildTermsForBatch( array $propertyIds ) {
		foreach ( $propertyIds as $propertyId ) {
			$this->saveTerms(
				$this->propertyLookup->getPropertyForId( $propertyId )
			);
		}
	}

	private function saveTerms( Property $property ) {
		try {
			$this->propertyTermStoreWriter->storeTerms( $property->getId(), $property->getFingerprint() );
		} catch ( Exception $ex ) {
			$this->loadBalancerFactory->rollbackMasterChanges( __METHOD__ );
			$this->errorReporter->reportMessage(
				'Failed to save terms of property: ' . $property->getId()->getSerialization()
			);
			return;
		}
		$this->loadBalancerFactory->commitMasterChanges( __METHOD__ );
	}

}
