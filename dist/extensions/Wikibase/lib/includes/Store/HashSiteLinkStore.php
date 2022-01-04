<?php

namespace Wikibase\Lib\Store;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\SiteLink;
use Wikimedia\Assert\Assert;

/**
 * An array based dummy implementation of a SiteLinkStore for exclusive use in tests.
 * @todo Should be moved to the tests folder and namespace.
 *
 * @license GPL-2.0-or-later
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class HashSiteLinkStore implements SiteLinkStore {

	/**
	 * @var SiteLink[][] indexed by prefixed ItemId
	 */
	private $linksByItemId = [];

	/**
	 * @var ItemId[] indexed by SiteLink link text "siteid:title"
	 */
	private $itemIdsByLink = [];

	/**
	 * @see SiteLinkStore::getItemIdForLink
	 *
	 * @param string $globalSiteId
	 * @param string $pageTitle
	 *
	 * @throws InvalidArgumentException if a parameter does not have the expected type
	 * @return ItemId|null
	 */
	public function getItemIdForLink( $globalSiteId, $pageTitle ) {
		Assert::parameterType( 'string', $globalSiteId, '$globalSiteId' );
		Assert::parameterType( 'string', $pageTitle, '$pageTitle' );

		$key = "$globalSiteId:$pageTitle";
		return $this->itemIdsByLink[$key] ?? null;
	}

	public function getLinks(
		?array $numericIds = null,
		?array $siteIds = null,
		?array $pageNames = null
	): array {
		$links = [];

		foreach ( $this->linksByItemId as $prefixedId => $siteLinks ) {
			/** @var SiteLink[] $siteLinks */
			'@phan-var SiteLink[] $siteLinks';
			foreach ( $siteLinks as $siteLink ) {
				$itemId = new ItemId( $prefixedId );

				if ( $this->linkMatches( $itemId, $siteLink, $numericIds, $siteIds, $pageNames ) ) {
					$links[] = [
						$siteLink->getSiteId(),
						$siteLink->getPageName(),
						$itemId->getNumericId(),
					];
				}
			}
		}

		return $links;
	}

	/**
	 * Returns true if the link matches the given conditions.
	 *
	 * @param ItemId $itemId
	 * @param SiteLink $siteLink
	 * @param int[]|null $numericIds like for SiteLinkLookup::getLinks()
	 * @param string[]|null $siteIds like for SiteLinkLookup::getLinks()
	 * @param string[]|null $pageNames like for SiteLinkLookup::getLinks()
	 *
	 * @return bool
	 */
	private function linkMatches(
		ItemId $itemId,
		SiteLink $siteLink,
		?array $numericIds,
		?array $siteIds,
		?array $pageNames
	) {
		return ( $numericIds === null || in_array( $itemId->getNumericId(), $numericIds ) )
			&& ( $siteIds === null || in_array( $siteLink->getSiteId(), $siteIds ) )
			&& ( $pageNames === null || in_array( $siteLink->getPageName(), $pageNames ) );
	}

	/**
	 * @see SiteLinkStore::getSiteLinksForItem
	 *
	 * @param ItemId $itemId
	 *
	 * @return SiteLink[]
	 */
	public function getSiteLinksForItem( ItemId $itemId ) {
		$prefixedId = $itemId->getSerialization();

		if ( array_key_exists( $prefixedId, $this->linksByItemId ) ) {
			return $this->linksByItemId[$prefixedId];
		}

		return [];
	}

	/**
	 * @see SiteLinkStore::getItemIdForSiteLink
	 *
	 * @param SiteLink $siteLink
	 *
	 * @return ItemId|null
	 */
	public function getItemIdForSiteLink( SiteLink $siteLink ) {
		$siteLinkKey = $this->makeSiteLinkKey( $siteLink );

		if ( array_key_exists( $siteLinkKey, $this->itemIdsByLink ) ) {
			return $this->itemIdsByLink[$siteLinkKey];
		}

		return null;
	}

	/**
	 * @see SiteLinkStore::saveLinksOfItem
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function saveLinksOfItem( Item $item ) {
		$itemId = $item->getId();

		$this->deleteLinksOfItem( $itemId );

		foreach ( $item->getSiteLinkList() as $siteLink ) {
			$this->indexByLink( $itemId, $siteLink );
			$this->indexByItemId( $itemId, $siteLink );
		}

		return true;
	}

	/**
	 * See SiteLinkStore::deleteLinksOfItem
	 *
	 * @param ItemId $itemId
	 *
	 * @return bool
	 */
	public function deleteLinksOfItem( ItemId $itemId ) {
		$prefixedId = $itemId->getSerialization();
		$siteLinks = $this->getSiteLinksForItem( $itemId );

		foreach ( $siteLinks as $siteLink ) {
			$key = $this->makeSiteLinkKey( $siteLink );
			unset( $this->itemIdsByLink[$key] );
		}

		unset( $this->linksByItemId[$prefixedId] );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function clear() {
		$this->linksByItemId = [];
		$this->itemIdsByLink = [];
		return true;
	}

	private function indexByLink( ItemId $itemId, SiteLink $siteLink ) {
		$key = $this->makeSiteLinkKey( $siteLink );
		$this->itemIdsByLink[$key] = $itemId;
	}

	private function indexByItemId( ItemId $itemId, SiteLink $siteLink ) {
		$prefixedId = $itemId->getSerialization();
		$this->linksByItemId[$prefixedId][] = $siteLink;
	}

	/**
	 * @param SiteLink $siteLink
	 *
	 * @return string
	 */
	private function makeSiteLinkKey( SiteLink $siteLink ) {
		return $siteLink->getSiteId() . ':' . $siteLink->getPageName();
	}

	/**
	 * @param string $globalSiteId
	 * @param string $pageTitle
	 *
	 * @throws InvalidArgumentException if a parameter does not have the expected type
	 * @return EntityId|null
	 */
	public function getEntityIdForLinkedTitle( $globalSiteId, $pageTitle ) {
		return $this->getItemIdForLink( $globalSiteId, $pageTitle );
	}

}
