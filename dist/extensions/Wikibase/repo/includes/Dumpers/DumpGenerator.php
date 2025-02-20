<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Dumpers;

use InvalidArgumentException;
use LogicException;
use Onoi\MessageReporter\MessageReporter;
use Onoi\MessageReporter\NullMessageReporter;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Services\Entity\EntityPrefetcher;
use Wikibase\DataModel\Services\EntityId\EntityIdPager;
use Wikibase\DataModel\Services\Lookup\EntityLookupException;
use Wikibase\Lib\Reporting\ExceptionHandler;
use Wikibase\Lib\Reporting\RethrowingExceptionHandler;
use Wikibase\Lib\Store\StorageException;
use Wikibase\Repo\Store\Sql\SqlEntityIdPager;

/**
 * DumpGenerator generates a dump of a given set of entities, excluding
 * redirects.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
abstract class DumpGenerator {

	/**
	 * @var int The max number of entities to process in a single batch.
	 *      Also controls the interval for progress reports.
	 */
	protected int $batchSize = 100;

	/**
	 * @var resource File handle for output
	 */
	protected $out;

	/**
	 * @var int Total number of shards a request should be split into
	 */
	protected int $shardingFactor = 1;

	/**
	 * @var int Number of the requested shard
	 */
	protected int $shard = 0;

	protected MessageReporter $progressReporter;

	protected ExceptionHandler $exceptionHandler;

	protected EntityPrefetcher $entityPrefetcher;

	/**
	 * @var int[]|null String to int map of types to include.
	 */
	protected ?array $entityTypes = null;

	/**
	 * Entity count limit - dump will generate this many
	 */
	protected int $limit = 0;

	/** @var callable Callback called once per batch. */
	private $batchCallback;

	/**
	 * @param resource $out
	 * @param EntityPrefetcher $entityPrefetcher
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $out, EntityPrefetcher $entityPrefetcher ) {
		if ( !$out ) {
			throw new InvalidArgumentException( '$out must be a file handle!' );
		}

		$this->out = $out;

		$this->entityPrefetcher = $entityPrefetcher;
		$this->progressReporter = new NullMessageReporter();
		$this->exceptionHandler = new RethrowingExceptionHandler();
		$this->batchCallback = function () {
		};
	}

	/**
	 * Set maximum number of entities produced
	 */
	public function setLimit( int $limit ): void {
		$this->limit = $limit;
	}

	/**
	 * Sets the batch size for processing. The batch size is used as the limit
	 * when listing IDs via the EntityIdPager::getNextBatchOfIds() method, and
	 * also controls the interval of progress reports.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setBatchSize( int $batchSize ): void {
		if ( $batchSize < 1 ) {
			throw new InvalidArgumentException( '$batchSize must be an integer >= 1' );
		}

		$this->batchSize = $batchSize;
	}

	public function setProgressReporter( MessageReporter $progressReporter ) {
		$this->progressReporter = $progressReporter;
	}

	public function setExceptionHandler( ExceptionHandler $exceptionHandler ): void {
		$this->exceptionHandler = $exceptionHandler;
	}

	/**
	 * Set the sharding factor and desired shard.
	 * For instance, to generate four dumps in parallel, use setShardingFilter( 4, 0 )
	 * for the first dump, setShardingFilter( 4, 1 ) for the second dump, etc.
	 *
	 * @param int $shardingFactor
	 * @param int $shard
	 *
	 * @throws InvalidArgumentException
	 */
	public function setShardingFilter( $shardingFactor, $shard ): void {
		if ( !is_int( $shardingFactor ) || $shardingFactor < 1 ) {
			throw new InvalidArgumentException( '$shardingFactor must be a positive integer.' );
		}

		if ( !is_int( $shard ) || $shard < 0 ) {
			throw new InvalidArgumentException( '$shard must be a non-negative integer.' );
		}

		if ( $shard >= $shardingFactor ) {
			throw new InvalidArgumentException( '$shard must be smaller than $shardingFactor.' );
		}

		$this->shardingFactor = $shardingFactor;
		$this->shard = $shard;
	}

	/**
	 * Set the entity types to be included in the output.
	 *
	 * @param string[]|null $types The desired types (use null for any type).
	 */
	public function setEntityTypesFilter( ?array $types ): void {
		if ( is_array( $types ) ) {
			$types = array_flip( $types );
		}
		$this->entityTypes = $types;
	}

	/**
	 * Set a callback that is called once per batch, at the beginning of each batch.
	 */
	public function setBatchCallback( callable $callback ): void {
		$this->batchCallback = $callback;
	}

	private function idMatchesFilters( EntityId $entityId ): bool {
		return $this->idMatchesShard( $entityId ) && $this->idMatchesType( $entityId );
	}

	private function idMatchesShard( EntityId $entityId ): bool {
		// Shorten out
		if ( $this->shardingFactor === 1 ) {
			return true;
		}

		$hash = sha1( $entityId->getSerialization() );
		$shard = (int)hexdec( substr( $hash, 0, 8 ) ); // 4 bytes of the hash
		$shard = abs( $shard ); // avoid negative numbers on 32 bit systems
		$shard %= $this->shardingFactor; // modulo number of shards

		return $shard === $this->shard;
	}

	private function idMatchesType( EntityId $entityId ): bool {
		return $this->entityTypes === null || ( array_key_exists( $entityId->getEntityType(), $this->entityTypes ) );
	}

	/**
	 * Writers the given string to the output provided to the constructor.
	 */
	protected function writeToDump( string $data ): void {
		//TODO: use output stream object
		fwrite( $this->out, $data );
	}

	/**
	 * Do something before dumping data
	 */
	protected function preDump(): void {
		// Nothing by default
	}

	/**
	 * Do something after dumping data
	 */
	protected function postDump(): void {
		// Nothing by default
	}

	/**
	 * Do something before dumping a batch of entities
	 * @param EntityId[] $entities
	 */
	protected function preBatchDump( array $entities ): void {
		$this->entityPrefetcher->prefetch( $entities );
	}

	/**
	 * Do something before dumping entity
	 */
	protected function preEntityDump( int $dumpCount ): void {
		// Nothing by default
	}

	/**
	 * Do something after dumping entity
	 */
	protected function postEntityDump( int $dumpCount ): void {
		// Nothing by default
	}

	/**
	 * Generates a dump, writing to the file handle provided to the constructor.
	 */
	public function generateDump( EntityIdPager $idPager ): void {
		$dumpCount = 0;

		$this->preDump();

		// Iterate over batches of IDs, maintaining the current position of the pager in the $position variable.
		while ( true ) {
			( $this->batchCallback )();

			if ( $this->limit && ( $dumpCount + $this->batchSize ) > $this->limit ) {
				// Try not to overrun $limit in order to make sure pager's position can be used for continuing.
				$limit = $this->limit - $dumpCount;
			} else {
				$limit = $this->batchSize;
			}

			$ids = $idPager->fetchIds( $limit );
			if ( !$ids ) {
				break;
			}

			$this->dumpEntities( $ids, $dumpCount );

			$this->progressReporter->reportMessage( 'Processed ' . $dumpCount . ' entities.' );

			if ( $this->limit && $dumpCount >= $this->limit ) {
				$this->progressReporter->reportMessage( 'Reached entity dump limit of ' . $this->limit . '.' );

				if ( $idPager instanceof SqlEntityIdPager ) {
					// This message is possibly being parsed for continuation purposes, thus avoid changing it.
					$this->progressReporter->reportMessage( 'Last SqlEntityIdPager position: ' . $idPager->getPosition() . '.' );
				}

				break;
			}
		}

		$this->postDump();
	}

	/**
	 * Dump list of entities
	 *
	 * @param EntityId[] $entityIds
	 * @param int &$dumpCount The number of entities already dumped (will be updated).
	 */
	private function dumpEntities( array $entityIds, int &$dumpCount ): void {
		$toLoad = [];
		foreach ( $entityIds as $entityId ) {
			if ( $this->idMatchesFilters( $entityId ) ) {
				$toLoad[] = $entityId;
			}
		}

		$this->preBatchDump( $toLoad );

		foreach ( $toLoad as $entityId ) {
			try {
				$data = $this->generateDumpForEntityId( $entityId );
				if ( !$data ) {
					continue;
				}

				$this->preEntityDump( $dumpCount );
				$this->writeToDump( $data );
				$this->postEntityDump( $dumpCount );

				$dumpCount++;
				if ( $this->limit && $dumpCount >= $this->limit ) {
					break;
				}
			} catch ( EntityLookupException | StorageException | LogicException $ex ) {
				$this->exceptionHandler->handleException( $ex, 'failed-to-dump', 'Failed to dump ' . $entityId );
			}
		}
	}

	/**
	 * Produce dump data for specific entity
	 *
	 * @param EntityId $entityId
	 *
	 * @throws EntityLookupException
	 * @throws StorageException
	 * @return string|null
	 */
	abstract protected function generateDumpForEntityId( EntityId $entityId ): ?string;

}
