<?php

namespace Wikibase\Client\Specials;

use Html;
use NamespaceInfo;
use QueryPage;
use Skin;
use Title;
use TitleFactory;
use Wikibase\Client\NamespaceChecker;
use Wikibase\Lib\Rdbms\ClientDomainDb;
use Wikibase\Lib\Rdbms\ClientDomainDbFactory;
use Wikimedia\Rdbms\FakeResultWrapper;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * List client pages that are not connected to repository items.
 *
 * @license GPL-2.0-or-later
 * @author John Erling Blad < jeblad@gmail.com >
 * @author Amir Sarabadani < ladsgroup@gmail.com >
 * @author Daniel Kinzler
 */
class SpecialUnconnectedPages extends QueryPage {

	/**
	 * @var int maximum supported offset
	 */
	private const MAX_OFFSET = 10000;

	/** @var NamespaceInfo */
	private $namespaceInfo;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var NamespaceChecker */
	private $namespaceChecker;

	/** @var ClientDomainDb */
	private $db;

	public function __construct(
		NamespaceInfo $namespaceInfo,
		TitleFactory $titleFactory,
		ClientDomainDbFactory $db,
		NamespaceChecker $namespaceChecker
	) {
		parent::__construct( 'UnconnectedPages' );
		$this->namespaceInfo = $namespaceInfo;
		$this->titleFactory = $titleFactory;
		$this->namespaceChecker = $namespaceChecker;
		$this->db = $db->newLocalDb();
		$this->setDBLoadBalancer( $this->db->loadBalancer() );
	}

	/**
	 * @see QueryPage::isSyndicated
	 *
	 * @return bool Always false because we do not want to build RSS/Atom feeds for this page.
	 */
	public function isSyndicated() {
		return false;
	}

	/**
	 * @see QueryPage::isCacheable
	 *
	 * @return bool Always false because we can not have caching since we will store additional information.
	 */
	public function isCacheable() {
		return false;
	}

	/**
	 * Build conditionals for namespace
	 *
	 * @param IDatabase $dbr
	 * @param Title|null $title
	 *
	 * @return string[]
	 */
	public function buildConditionals( IDatabase $dbr, Title $title = null ) {
		$conds = [];

		if ( $title !== null ) {
			$conds[] = 'page_title >= ' . $dbr->addQuotes( $title->getDBkey() );
			$conds[] = 'page_namespace = ' . $title->getNamespace();
		}
		$wbNamespaces = $this->namespaceChecker->getWikibaseNamespaces();
		$ns = $this->getRequest()->getIntOrNull( 'namespace' );
		if ( $ns !== null && in_array( $ns, $wbNamespaces ) ) {
			$conds[] = 'page_namespace = ' . $ns;
		} else {
			$conds[] = 'page_namespace IN (' . implode( ',', $wbNamespaces ) . ')';
		}

		return $conds;
	}

	/**
	 * @see QueryPage::getQueryInfo
	 *
	 * @return array[]
	 */
	public function getQueryInfo() {
		$dbr = $this->db->connections()->getReadConnectionRef();

		$conds = $this->buildConditionals( $dbr );
		$conds['page_is_redirect'] = 0;
		$conds[] = 'pp_propname IS NULL';

		return [
			'tables' => [
				'page',
				'page_props',
			],
			'fields' => [
				'value' => 'page_id',
				'namespace' => 'page_namespace',
				'title' => 'page_title',
				// Placeholder, we'll get this from page_props in the future.
				'page_num_iwlinks' => '0',
			],
			'conds' => $conds,
			// Sorting is determined getOrderFields(), which returns [ 'value' ] per default.
			'options' => [],
			'join_conds' => [
				// TODO Also get explicit_langlink_count from page_props once that is populated.
				// Could even filter or sort by it via pp_sortkey.
				'page_props' => [
					'LEFT JOIN',
					[ 'page_id = pp_page', 'pp_propname' => [ 'wikibase_item', 'expectedUnconnectedPage' ] ]
				],
			]
		];
	}

	/**
	 * @see QueryPage::reallyDoQuery
	 *
	 * @param int|bool $limit
	 * @param int|bool $offset
	 *
	 * @return IResultWrapper
	 */
	public function reallyDoQuery( $limit, $offset = false ) {
		if ( is_int( $offset ) && $offset > self::MAX_OFFSET ) {
			return new FakeResultWrapper( [] );
		}

		return parent::reallyDoQuery( $limit, $offset );
	}

	/**
	 * @see QueryPage::formatResult
	 *
	 * @param Skin $skin
	 * @param object $result
	 *
	 * @return string|bool
	 */
	public function formatResult( $skin, $result ) {
		$title = $this->titleFactory->newFromID( $result->value );
		if ( $title === null ) {
			return false;
		}

		$out = $this->getLinkRenderer()->makeKnownLink( $title );

		if ( $result->page_num_iwlinks > 0 ) {
			$out .= ' ' . $this->msg( 'wikibase-unconnectedpages-format-row' )
				->numParams( $result->page_num_iwlinks )->text();
		}

		return $out;
	}

	/**
	 * @see QueryPage::getPageHeader
	 *
	 * @return string
	 */
	public function getPageHeader() {
		$excludeNamespaces = array_diff(
			$this->namespaceInfo->getValidNamespaces(),
			$this->namespaceChecker->getWikibaseNamespaces()
		);

		$limit = $this->getRequest()->getIntOrNull( 'limit' );
		$ns = $this->getRequest()->getIntOrNull( 'namespace' );

		return Html::openElement(
			'form',
			[
				'action' => $this->getPageTitle()->getLocalURL()
			]
		) .
		( $limit === null ? '' : Html::hidden( 'limit', $limit ) ) .
		Html::namespaceSelector( [
			'selected' => $ns === null ? '' : $ns,
			'all' => '',
			'exclude' => $excludeNamespaces,
			'label' => $this->msg( 'namespace' )->text()
		] ) . ' ' .
		Html::submitButton(
			$this->msg( 'wikibase-unconnectedpages-submit' )->text(),
			[]
		) .
		Html::closeElement( 'form' );
	}

	/**
	 * @see SpecialPage::getGroupName
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'maintenance';
	}

	/**
	 * @see QueryPage::linkParameters
	 *
	 * @return array
	 */
	public function linkParameters() {
		return [
			'namespace' => $this->getRequest()->getIntOrNull( 'namespace' ),
		];
	}

}
