<?php

namespace Wikibase\Repo\Store\Sql;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\EntityId\EntityIdComposer;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\Lib\Rdbms\RepoDomainDb;
use Wikibase\Repo\Store\SiteLinkConflictLookup;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class SqlSiteLinkConflictLookup implements SiteLinkConflictLookup {

	/** @var RepoDomainDb */
	private $db;

	/**
	 * @var EntityIdComposer
	 */
	private $entityIdComposer;

	public function __construct(
		RepoDomainDb $db,
		EntityIdComposer $entityIdComposer
	) {
		$this->db = $db;
		$this->entityIdComposer = $entityIdComposer;
	}

	/**
	 * @see SiteLinkConflictLookup::getConflictsForItem
	 *
	 * @param ItemId $itemId
	 * @param SiteLinkList $siteLinkList
	 * @param int|null $db
	 *
	 * @return array[] An array of arrays, each with the keys "siteId", "itemId" and "sitePage".
	 */
	public function getConflictsForItem( ItemId $itemId, SiteLinkList $siteLinkList, ?int $db = null ) {
		if ( $siteLinkList->isEmpty() ) {
			return [];
		}

		if ( !$db || $db === DB_REPLICA ) {
			$dbr = $this->db->connections()->getReadConnection();
		} elseif ( $db === DB_PRIMARY ) {
			// CONN_TRX_AUTOCOMMIT: ensure we can read rows (i.e. get conflicts)
			// that were committed after the main transaction started (T291377)
			$dbr = $this->db->connections()->getWriteConnection( ILoadBalancer::CONN_TRX_AUTOCOMMIT );
		} else {
			throw new InvalidArgumentException( '$db must be either DB_REPLICA or DB_PRIMARY' );
		}

		$linkConds = [];

		foreach ( $siteLinkList as $siteLink ) {
			$linkConds[] = $dbr->andExpr( [
				'ips_site_id' => $siteLink->getSiteId(),
				'ips_site_page' => $siteLink->getPageName(),
			] );
		}

		// TODO: $linkConds might get very large and hit some size limit imposed
		//       by the database. We could chop it up of we know that size limit.
		//       For MySQL, it's select @@max_allowed_packet.

		$conflictingLinks = $dbr->newSelectQueryBuilder()
			->select( [
				'ips_site_id',
				'ips_site_page',
				'ips_item_id',
			] )
			->from( 'wb_items_per_site' )
			->where( $dbr->orExpr( $linkConds ) )
			->andWhere( $dbr->expr( 'ips_item_id', '!=', $itemId->getNumericId() ) )
			->caller( __METHOD__ )
			->fetchResultSet();

		$conflicts = [];

		foreach ( $conflictingLinks as $link ) {
			$conflicts[] = [
				'siteId' => $link->ips_site_id,
				'itemId' => $this->entityIdComposer->composeEntityId(
					Item::ENTITY_TYPE,
					$link->ips_item_id
				),
				'sitePage' => $link->ips_site_page,
			];
		}

		return $conflicts;
	}

}
