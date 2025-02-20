<?php

declare( strict_types = 1 );

namespace Wikibase\Lib\Store\Sql;

use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Lib\Changes\EntityChange;
use Wikibase\Lib\Changes\EntityChangeFactory;
use Wikibase\Lib\Rdbms\RepoDomainDb;
use Wikimedia\Assert\Assert;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Allows accessing changes stored in a database.
 *
 * @license GPL-2.0-or-later
 * @author Marius Hoch
 */
class EntityChangeLookup {

	private EntityChangeFactory $entityChangeFactory;

	private EntityIdParser $entityIdParser;

	private RepoDomainDb $db;

	public function __construct(
		EntityChangeFactory $entityChangeFactory,
		EntityIdParser $entityIdParser,
		RepoDomainDb $db
	) {
		$this->entityChangeFactory = $entityChangeFactory;
		$this->entityIdParser = $entityIdParser;
		$this->db = $db;
	}

	/**
	 * @param int[] $ids
	 *
	 * @return EntityChange[]
	 */
	public function loadByChangeIds( array $ids ): array {
		Assert::parameterElementType( 'integer', $ids, '$ids' );

		$dbr = $this->db->connections()->getReadConnection();
		return $this->newEntityChangeSelectQueryBuilder( $dbr )
			->where( [ 'change_id' => $ids ] )
			->caller( __METHOD__ )
			->fetchChanges();
	}

	/**
	 * @param string $entityId
	 *
	 * @return EntityChange[]
	 */
	public function loadByEntityIdFromPrimary( string $entityId ): array {
		$dbw = $this->db->connections()->getWriteConnection();
		return $this->newEntityChangeSelectQueryBuilder( $dbw )
			->where( [ 'change_object_id' => $entityId ] )
			->caller( __METHOD__ )
			->fetchChanges();
	}

	/**
	 * @param string $thisTimeOrOlder maximum timestamp of changes to returns (TS_MW format)
	 * @param int $batchSize maximum number of changes to return
	 * @param int $offset skip this many changes
	 *
	 * @return EntityChange[]
	 */
	public function loadChangesBefore( string $thisTimeOrOlder, int $batchSize, int $offset ): array {
		$dbr = $this->db->connections()->getReadConnection();
		return $this->newEntityChangeSelectQueryBuilder( $dbr )
			->where( $dbr->expr( 'change_time', '<=', $dbr->timestamp( $thisTimeOrOlder ) ) )
			->limit( $batchSize )
			->offset( $offset )
			->caller( __METHOD__ )
			->fetchChanges();
	}

	private function newEntityChangeSelectQueryBuilder( IReadableDatabase $db ): EntityChangeSelectQueryBuilder {
		return new EntityChangeSelectQueryBuilder(
			$db,
			$this->entityIdParser,
			$this->entityChangeFactory
		);
	}

}
