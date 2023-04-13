<?php

namespace Wikibase\Repo\Store\Sql;

use MWException;
use Title;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Services\Lookup\EntityRedirectLookup;
use Wikibase\DataModel\Services\Lookup\EntityRedirectLookupException;
use Wikibase\Lib\Rdbms\RepoDomainDb;
use Wikibase\Lib\Store\EntityIdLookup;
use Wikibase\Repo\Store\EntityTitleStoreLookup;

/**
 * @license GPL-2.0-or-later
 * @author Marius Hoch
 */
class WikiPageEntityRedirectLookup implements EntityRedirectLookup {

	/**
	 * @var EntityTitleStoreLookup
	 */
	private $entityTitleLookup;

	/**
	 * @var EntityIdLookup
	 */
	private $entityIdLookup;

	/** @var RepoDomainDb */
	private $repoDb;

	public function __construct(
		EntityTitleStoreLookup $entityTitleLookup,
		EntityIdLookup $entityIdLookup,
		RepoDomainDb $repoDb
	) {
		$this->entityTitleLookup = $entityTitleLookup;
		$this->entityIdLookup = $entityIdLookup;
		$this->repoDb = $repoDb;
	}

	/**
	 * Returns the IDs that redirect to (are aliases of) the given target entity.
	 *
	 * @param EntityId $targetId
	 *
	 * @return EntityId[]
	 * @throws EntityRedirectLookupException
	 */
	public function getRedirectIds( EntityId $targetId ) {
		try {
			$title = $this->entityTitleLookup->getTitleForId( $targetId );
		} catch ( \Exception $ex ) {
			// TODO: Catch more specific type of exception once EntityTitleStoreLookup contract is clarified
			throw new EntityRedirectLookupException( $targetId, null, $ex );
		}

		try {
			$dbr = $this->repoDb->connections()->getReadConnectionRef();
		} catch ( MWException $ex ) {
			throw new EntityRedirectLookupException( $targetId, null, $ex );
		}

		$res = $dbr->select(
			[ 'page', 'redirect' ],
			[ 'page_namespace', 'page_title' ],
			[
				'rd_title' => $title->getDBkey(),
				'rd_namespace' => $title->getNamespace(),
				// Entity redirects are guaranteed to be in the same namespace
				'page_namespace' => $title->getNamespace(),
			],
			__METHOD__,
			[
				'LIMIT' => 1000 // everything should have a hard limit
			],
			[
				'redirect' => [ 'JOIN', 'rd_from=page_id' ],
			]
		);

		if ( !$res ) {
			return [];
		}

		$ids = [];
		foreach ( $res as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );

			$ids[] = $this->entityIdLookup->getEntityIdForTitle( $title );
		}

		return $ids;
	}

	/**
	 * @see EntityRedirectLookup::getRedirectForEntityId
	 *
	 * @param EntityId $entityId
	 * @param string $forUpdate
	 *
	 * @return EntityId|null The ID of the redirect target, or null if $entityId
	 *         does not refer to a redirect
	 * @throws EntityRedirectLookupException
	 */
	public function getRedirectForEntityId( EntityId $entityId, $forUpdate = '' ) {
		try {
			$title = $this->entityTitleLookup->getTitleForId( $entityId );
		} catch ( \Exception $ex ) {
			// TODO: Catch more specific type of exception once EntityTitleStoreLookup contract is clarified
			throw new EntityRedirectLookupException( $entityId, null, $ex );
		}

		try {
			if ( $forUpdate === EntityRedirectLookup::FOR_UPDATE ) {
				$db = $this->repoDb->connections()->getWriteConnectionRef();
			} else {
				$db = $this->repoDb->connections()->getReadConnectionRef();
			}
		} catch ( MWException $ex ) {
			throw new EntityRedirectLookupException( $entityId, null, $ex );
		}

		$row = $db->selectRow(
			[ 'page', 'redirect' ],
			[ 'page_id', 'rd_namespace', 'rd_title' ],
			[
				'page_title' => $title->getDBkey(),
				'page_namespace' => $title->getNamespace()
			],
			__METHOD__,
			[],
			[
				'redirect' => [ 'LEFT JOIN', 'rd_from=page_id' ]
			]
		);

		if ( !$row ) {
			throw new EntityRedirectLookupException( $entityId );
		}

		if ( $row->rd_namespace === null ) {
			return null;
		}

		$title = Title::makeTitle( $row->rd_namespace, $row->rd_title );

		return $this->entityIdLookup->getEntityIdForTitle( $title );
	}

}
