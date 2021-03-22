<?php

namespace Wikibase\Repo\Store;

use Exception;
use Onoi\MessageReporter\MessageReporter;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Services\Lookup\ItemLookup;
use Wikibase\DataModel\Services\Lookup\ItemLookupException;
use Wikibase\DataModel\Services\Term\ItemTermStoreWriter;
use Wikibase\Lib\Store\RevisionedUnresolvedRedirectException;
use Wikimedia\Rdbms\ILBFactory;

/**
 * @license GPL-2.0-or-later
 */
class ItemTermsRebuilder {

	/** @var ItemTermStoreWriter */
	private $itemTermStoreWriter;
	/* @var iterable */
	private $itemIds;
	/** @var MessageReporter */
	private $progressReporter;
	/** @var MessageReporter */
	private $errorReporter;
	/** @var ILBFactory */
	private $loadBalancerFactory;
	/** @var ItemLookup */
	private $itemLookup;
	/** @var int */
	private $batchSize;
	/** @var int */
	private $batchSpacingInSeconds;

	/**
	 * @param ItemTermStoreWriter $itemTermStoreWriter
	 * @param $itemIdIterable
	 * @param MessageReporter $progressReporter
	 * @param MessageReporter $errorReporter
	 * @param ILBFactory $loadBalancerFactory
	 * @param ItemLookup $itemLookup
	 * @param int $batchSize
	 * @param int $batchSpacingInSeconds
	 */
	public function __construct(
		ItemTermStoreWriter $itemTermStoreWriter,
		$itemIdIterable,
		MessageReporter $progressReporter,
		MessageReporter $errorReporter,
		ILBFactory $loadBalancerFactory,
		ItemLookup $itemLookup,
		$batchSize,
		$batchSpacingInSeconds
	) {
		$this->itemTermStoreWriter = $itemTermStoreWriter;
		$this->itemIds = $itemIdIterable;
		$this->progressReporter = $progressReporter;
		$this->errorReporter = $errorReporter;
		$this->loadBalancerFactory = $loadBalancerFactory;
		$this->itemLookup = $itemLookup;
		$this->batchSize = $batchSize;
		$this->batchSpacingInSeconds = $batchSpacingInSeconds;
	}

	public function rebuild() {
		$ticket = $this->loadBalancerFactory->getEmptyTransactionTicket( __METHOD__ );

		foreach ( $this->getIdBatches() as $itemIds ) {
			$this->progressReporter->reportMessage(
				'Rebuilding ' . $itemIds[0] . ' till ' . end( $itemIds )
			);

			$this->rebuildTermsForBatch( $itemIds );

			$success = $this->loadBalancerFactory->commitAndWaitForReplication( __METHOD__, $ticket );
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

	private function getIdBatches() {
		$idsInBatch = [];

		foreach ( $this->itemIds as $itemId ) {
			$idsInBatch[] = $itemId;

			if ( count( $idsInBatch ) >= $this->batchSize ) {
				yield $idsInBatch;
				$idsInBatch = [];
			}
		}

		if ( $idsInBatch !== [] ) {
			yield $idsInBatch;
		}
	}

	private function rebuildTermsForBatch( array $itemIds ) {
		foreach ( $itemIds as $itemId ) {
			try {
				$item = $this->itemLookup->getItemForId( $itemId );
			} catch ( RevisionedUnresolvedRedirectException $entityRedirectException ) {
				// Nothing to do, ignore
				continue;
			} catch ( ItemLookupException $exception ) {
				// Unresolved redirects, nothing to do.
				continue;
			}

			if ( $item !== null ) {
				$this->saveTerms( $item );
			}
		}
	}

	private function saveTerms( Item $item ) {
		try {
			$this->itemTermStoreWriter->storeTerms( $item->getId(), $item->getFingerprint() );
		} catch ( Exception $ex ) {
			$this->loadBalancerFactory->rollbackMasterChanges( __METHOD__ );
			$this->errorReporter->reportMessage(
				'Failed to save terms of item: ' . $item->getId()->getSerialization()
			);
			return;
		}
		$this->loadBalancerFactory->commitMasterChanges( __METHOD__ );
	}

}
