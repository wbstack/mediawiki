<?php

declare( strict_types = 1 );

namespace Wikibase\Client\Usage\Sql;

use ArrayIterator;
use InvalidArgumentException;
use Traversable;
use Wikibase\Client\Usage\EntityUsage;
use Wikibase\Client\Usage\UsageLookup;
use Wikibase\Client\Usage\UsageTracker;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\SessionConsistentConnectionManager;

/**
 * An SQL based usage tracker implementation.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class SqlUsageTracker implements UsageTracker, UsageLookup {

	private EntityIdParser $idParser;

	private SessionConsistentConnectionManager $connectionManager;

	/**
	 * Usage aspects in this array won't be persisted. If string keys are used, this
	 * is treated as [ 'usage-aspect-to-replace' => 'replacement' ].
	 *
	 * @var string[]
	 */
	private array $disabledUsageAspects;

	/**
	 * The limit to issue a warning when entity usage per page hit that limit
	 */
	private int $entityUsagePerPageLimit;

	/**
	 * The batch size when adding entity usage.
	 */
	private int $addEntityUsagesBatchSize;

	/**
	 * @param EntityIdParser $idParser
	 * @param SessionConsistentConnectionManager $connectionManager
	 * @param string[] $disabledUsageAspects
	 * @param int $entityUsagePerPageLimit
	 * @param int $addEntityUsagesBatchSize
	 */
	public function __construct(
		EntityIdParser $idParser,
		SessionConsistentConnectionManager $connectionManager,
		array $disabledUsageAspects,
		int $entityUsagePerPageLimit,
		int $addEntityUsagesBatchSize = 500
	) {
		$this->idParser = $idParser;
		$this->connectionManager = $connectionManager;
		$this->disabledUsageAspects = $disabledUsageAspects;
		$this->entityUsagePerPageLimit = $entityUsagePerPageLimit;
		$this->addEntityUsagesBatchSize = $addEntityUsagesBatchSize;
	}

	private function newUsageTable( ?IDatabase $db = null ): EntityUsageTable {
		$entityUsageTable = new EntityUsageTable( $this->idParser, $db );
		$entityUsageTable->setAddUsagesBatchSize( $this->addEntityUsagesBatchSize );
		return $entityUsageTable;
	}

	/**
	 * Re-indexes the given list of EntityUsages so that each EntityUsage can be found by using its
	 * string representation as a key.
	 *
	 * @param EntityUsage[] $usages
	 *
	 * @throws InvalidArgumentException
	 * @return EntityUsage[]
	 */
	private function reindexEntityUsages( array $usages ): array {
		$reindexed = [];

		foreach ( $usages as $usage ) {
			if ( !( $usage instanceof EntityUsage ) ) {
				throw new InvalidArgumentException( '$usages must contain EntityUsage objects.' );
			}

			$key = $usage->getIdentityString();
			$reindexed[$key] = $usage;
		}

		return $reindexed;
	}

	/**
	 * @param EntityUsage[] $usages
	 *
	 * @throws InvalidArgumentException
	 * @return EntityUsage[]
	 */
	private function handleDisabledUsages( array $usages ): array {
		$newUsages = [];

		foreach ( $usages as $usage ) {
			if ( !( $usage instanceof EntityUsage ) ) {
				throw new InvalidArgumentException( '$usages must contain EntityUsage objects.' );
			}

			// Disabled usage with replacement
			if ( isset( $this->disabledUsageAspects[$usage->getAspect()] ) ) {
				$newUsages[] = new EntityUsage( $usage->getEntityId(), $this->disabledUsageAspects[$usage->getAspect()] );
				continue;
			}

			// Disabled usage aspects without replacement (integer key, no replace from -> to map)
			if ( is_int( array_search( $usage->getAspect(), $this->disabledUsageAspects ) ) ) {
				continue;
			}

			$newUsages[] = $usage;
		}

		return $newUsages;
	}

	/**
	 * @see UsageTracker::addUsedEntities
	 *
	 * @param int $pageId
	 * @param EntityUsage[] $usages
	 *
	 * @throws InvalidArgumentException
	 */
	public function addUsedEntities( int $pageId, array $usages ): void {
		if ( count( $usages ) > $this->entityUsagePerPageLimit ) {
			wfLogWarning(
				'Number of usages in page id ' . $pageId . ' is too high: ' . count( $usages )
			);
		}

		$usages = $this->handleDisabledUsages( $usages );
		if ( !$usages ) {
			return;
		}

		// NOTE: while logically we'd like the below to be atomic, we don't wrap it in a
		// transaction to prevent long lock retention during big updates.
		$db = $this->connectionManager->getWriteConnection();
		$usageTable = $this->newUsageTable( $db );
		// queryUsages guarantees this to be identity string => EntityUsage
		$oldUsages = $usageTable->queryUsages( $pageId );

		$newUsages = $this->reindexEntityUsages( $usages );

		$added = $this->compareAndReturnNewUsagesAccountingForDeduplication( $newUsages, $oldUsages );
		// Actually add the new entries
		$usageTable->addUsages( $pageId, $added );

		$removed = $this->findStatementsPrunedByDeduplication( $newUsages, $oldUsages );
		// And remove any usages now made redundant by deduplication
		$usageTable->removeUsages( $pageId, $removed );
	}

	private function findStatementsPrunedByDeduplication( array $newUsages, array $oldUsages ): array {
		$result = [];
		foreach ( $oldUsages as $oldIdentity => $oldUsage ) {
			if (
				EntityUsage::stripModifier( $oldIdentity ) !== $oldIdentity &&
				array_key_exists( EntityUsage::stripModifier( $oldIdentity ), $newUsages )
			) {
				$result[ $oldIdentity ] = $oldUsage;
			}
		}
		return $result;
	}

	private function compareAndReturnNewUsagesAccountingForDeduplication(
		array $newUsages,
		array $oldUsages
	): array {
		$result = [];
		foreach ( $newUsages as $identity => $newUsage ) {
			if ( !array_key_exists( $identity, $oldUsages ) ) {
				$withoutModifier = EntityUsage::stripModifier( $identity );
				if ( !array_key_exists( $withoutModifier, $oldUsages ) ) {
					$result[ $identity ] = $newUsage;
				}
			}
		}
		return $result;
	}

	/**
	 * @see UsageTracker::replaceUsedEntities
	 *
	 * @param int $pageId
	 * @param EntityUsage[] $usages
	 *
	 * @return EntityUsage[] Usages that have been removed
	 *
	 * @throws InvalidArgumentException
	 */
	public function replaceUsedEntities( int $pageId, array $usages ): array {
		// NOTE: while logically we'd like the below to be atomic, we don't wrap it in a
		// transaction to prevent long lock retention during big updates.
		$db = $this->connectionManager->getWriteConnection();
		$usageTable = $this->newUsageTable( $db );
		// queryUsages guarantees this to be identity string => EntityUsage
		$oldUsages = $usageTable->queryUsages( $pageId );

		$usages = $this->handleDisabledUsages( $usages );
		$newUsages = $this->reindexEntityUsages( $usages );

		$removed = array_diff_key( $oldUsages, $newUsages );
		$added = array_diff_key( $newUsages, $oldUsages );

		$usageTable->removeUsages( $pageId, $removed );
		$usageTable->addUsages( $pageId, $added );
		return $removed;
	}

	/**
	 * @see UsageTracker::pruneUsages
	 *
	 * @param int $pageId
	 *
	 * @return EntityUsage[]
	 */
	public function pruneUsages( int $pageId ): array {
		// NOTE: while logically we'd like the below to be atomic, we don't wrap it in a
		// transaction to prevent long lock retention during big updates.
		$db = $this->connectionManager->getWriteConnection();
		$usageTable = $this->newUsageTable( $db );
		$pruned = $usageTable->pruneUsages( $pageId );

		return $pruned;
	}

	/**
	 * @see UsageLookup::getUsagesForPage
	 *
	 * @param int $pageId
	 *
	 * @return EntityUsage[] EntityUsage identity string => EntityUsage
	 */
	public function getUsagesForPage( int $pageId ): array {
		$usageTable = $this->newUsageTable();
		$usages = $usageTable->queryUsages( $pageId );

		return $usages;
	}

	/**
	 * @see UsageLookup::getPagesUsing
	 *
	 * @param EntityId[] $entityIds
	 * @param string[] $aspects
	 *
	 * @return Traversable A traversable over PageEntityUsages grouped by page.
	 */
	public function getPagesUsing( array $entityIds, array $aspects = [] ): Traversable {
		if ( !$entityIds ) {
			return new ArrayIterator();
		}

		$usageTable = $this->newUsageTable();
		$pages = $usageTable->getPagesUsing( $entityIds, $aspects );

		return $pages;
	}

	/**
	 * @see UsageLookup::getUnusedEntities
	 *
	 * @param EntityId[] $entityIds
	 *
	 * @return EntityId[]
	 */
	public function getUnusedEntities( array $entityIds ): array {
		if ( !$entityIds ) {
			return [];
		}

		$usageTable = $this->newUsageTable();
		$unused = $usageTable->getUnusedEntities( $entityIds );

		return $unused;
	}

}
