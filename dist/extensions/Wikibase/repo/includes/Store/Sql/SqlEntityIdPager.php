<?php

namespace Wikibase\Repo\Store\Sql;

use LinkCache;
use Title;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Services\EntityId\EntityIdPager;
use Wikibase\DataModel\Services\EntityId\SeekableEntityIdPager;
use Wikibase\Lib\Rdbms\RepoDomainDb;
use Wikibase\Lib\Store\EntityIdLookup;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikimedia\Assert\Assert;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * SqlEntityIdPager is a cursor for iterating over the EntityIds stored in
 * the current Wikibase installation.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 * @author Marius Hoch
 */
class SqlEntityIdPager implements SeekableEntityIdPager {

	/**
	 * @var EntityNamespaceLookup
	 */
	private $entityNamespaceLookup;

	/**
	 * @var string[]
	 */
	private $entityTypes;

	/**
	 * @var string
	 */
	private $redirectMode;

	/**
	 * Last page_id selected.
	 *
	 * @var int
	 */
	private $position = 0;

	/**
	 * Last page_id to fetch.
	 *
	 * @var int|null
	 */
	private $cutoffPosition = null;

	/**
	 * @var EntityIdLookup
	 */
	private $entityIdLookup;

	/**
	 * @var RepoDomainDb
	 */
	private $db;

	/**
	 * @var LinkCache|null
	 */
	private $linkCache;

	/**
	 * @param EntityNamespaceLookup $entityNamespaceLookup
	 * @param EntityIdLookup $entityIdLookup
	 * @param RepoDomainDb $repoDomainDb
	 * @param string[] $entityTypes The desired entity types, or empty array for any type.
	 * @param string $redirectMode A EntityIdPager::XXX_REDIRECTS constant (default is NO_REDIRECTS).
	 * @param LinkCache|null $linkCache
	 */
	public function __construct(
		EntityNamespaceLookup $entityNamespaceLookup,
		EntityIdLookup $entityIdLookup,
		RepoDomainDb $repoDomainDb,
		array $entityTypes = [],
		$redirectMode = EntityIdPager::NO_REDIRECTS,
		LinkCache $linkCache = null
	) {
		Assert::parameterElementType( 'string', $entityTypes, '$entityTypes' );

		$this->entityNamespaceLookup = $entityNamespaceLookup;
		$this->entityTypes = $entityTypes;
		$this->redirectMode = $redirectMode;
		$this->entityIdLookup = $entityIdLookup;
		$this->db = $repoDomainDb;
		$this->linkCache = $linkCache;
	}

	/**
	 * Fetches the next batch of IDs. Calling this has the side effect of advancing the
	 * internal state of the page, typically implemented by some underlying resource
	 * such as a file pointer or a database connection.
	 *
	 * @note After some finite number of calls, this method should eventually return
	 * an empty list of IDs, indicating that no more IDs are available.
	 *
	 * @param int $limit The maximum number of IDs to return.
	 *
	 * @return EntityId[] A list of EntityIds matching the given parameters. Will
	 * be empty if there are no more entities to list from the given offset.
	 */
	public function fetchIds( $limit ) {
		Assert::parameter( is_int( $limit ) && $limit > 0, '$limit', '$limit must be a positive integer' );
		$tables = [ 'page' ];
		if ( $this->redirectMode !== self::INCLUDE_REDIRECTS ) {
			$tables[] = 'redirect';
		}

		$orderBy = 'page_id ASC';
		if ( $this->redirectMode === self::ONLY_REDIRECTS ) {
			// Allow the SELECT to be based on the redirect table in this case,
			// rd_from equals page_id anyway.
			$orderBy = 'rd_from ASC';
		}

		$dbr = $this->db->connections()->getReadConnectionRef();
		$fields = array_unique( array_merge( LinkCache::getSelectFields(),
				[ 'page_id', 'page_title', 'page_namespace' ] ) );
		$rows = $dbr->select(
			$tables,
			$fields,
			$this->getWhere( $this->position ),
			__METHOD__,
			[
				'ORDER BY' => $orderBy,
				'LIMIT' => $limit
			],
			$this->getJoinConditions()
		);

		list( $entityIds, $position ) = $this->processRows( $rows );
		if ( $position !== null ) {
			$this->position = $position;
		}

		return $entityIds;
	}

	/**
	 * @return int The last page id fetched.
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @param int $position New pager position. Next fetch will start with page id $position + 1.
	 */
	public function setPosition( $position ) {
		$this->position = $position;
	}

	/**
	 * @param int|null $cutoffPosition The last page id that can be fetched. Null to allow fetching everything.
	 */
	public function setCutoffPosition( $cutoffPosition ) {
		$this->cutoffPosition = $cutoffPosition;
	}

	/**
	 * @param int $position
	 *
	 * @return array
	 */
	private function getWhere( $position ) {
		$where = [ 'page_id > ' . (int)$position ];

		if ( $this->cutoffPosition !== null ) {
			$where[] = 'page_id <= ' . (int)$this->cutoffPosition;
		}

		$where['page_namespace'] = $this->getEntityNamespaces( $this->entityTypes );

		if ( $this->redirectMode === self::NO_REDIRECTS ) {
			$where[] = 'rd_from IS NULL';
		}

		return $where;
	}

	private function getEntityNamespaces( array $entityTypes ) {
		if ( empty( $entityTypes ) ) {
			return $this->entityNamespaceLookup->getEntityNamespaces();
		}

		return array_map(
			[ $this->entityNamespaceLookup, 'getEntityNamespace' ],
			$entityTypes
		);
	}

	/**
	 * @return array
	 */
	private function getJoinConditions() {
		$joinConds = [];

		if ( $this->redirectMode === self::NO_REDIRECTS ) {
			$joinConds['redirect'] = [ 'LEFT JOIN', 'page_id = rd_from' ];
		} elseif ( $this->redirectMode === self::ONLY_REDIRECTS ) {
			$joinConds['redirect'] = [ 'INNER JOIN', 'page_id = rd_from' ];
		}

		return $joinConds;
	}

	/**
	 * Processes the query result: Parse the EntityIds and compute the last
	 * position. Returns an array with said entity ids and the next position
	 * or null in case the position didn't change.
	 *
	 * @param IResultWrapper $rows
	 *
	 * @return array Tuple with ( EntityId[], int|null )
	 */
	private function processRows( IResultWrapper $rows ) {
		$entityIds = [];
		$position = null;

		foreach ( $rows as $row ) {
			$position = (int)$row->page_id;
			$title = Title::newFromRow( $row );
			// Register with the cache so that getEntityIdForTitle and others can use it
			if ( $this->linkCache ) {
				$this->linkCache->addGoodLinkObjFromRow( $title, $row );
			}
			$entityId = $this->entityIdLookup->getEntityIdForTitle( $title );
			if ( $entityId ) {
				$entityIds[] = $entityId;
			}
		}

		return [ $entityIds, $position ];
	}

}
