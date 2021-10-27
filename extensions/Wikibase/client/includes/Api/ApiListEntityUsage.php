<?php

namespace Wikibase\Client\Api;

use ApiBase;
use ApiPageSet;
use ApiQuery;
use ApiQueryGeneratorBase;
use ApiResult;
use Title;
use Wikibase\Client\RepoLinker;
use Wikibase\Client\Usage\EntityUsage;
use Wikimedia\Rdbms\IResultWrapper;

/**
 * API module to get the usage of entities.
 *
 * @license GPL-2.0-or-later
 * @author Amir Sarabadani < ladsgroup@gmail.com >
 */
class ApiListEntityUsage extends ApiQueryGeneratorBase {

	/**
	 * @var RepoLinker
	 */
	private $repoLinker;

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 * @param RepoLinker $repoLinker
	 */
	public function __construct( ApiQuery $query, $moduleName, RepoLinker $repoLinker ) {
		parent::__construct( $query, $moduleName, 'wbeu' );

		$this->repoLinker = $repoLinker;
	}

	/**
	 * @see ApiQueryGeneratorBase::executeGenerator
	 *
	 * @param ApiPageSet $resultPageSet
	 */
	public function executeGenerator( $resultPageSet ) {
		$this->run( $resultPageSet );
	}

	public function execute() {
		$this->run();
	}

	/**
	 * @param ApiPageSet|null $resultPageSet
	 */
	public function run( ApiPageSet $resultPageSet = null ) {
		$params = $this->extractRequestParams();
		$res = $this->doQuery( $params, $resultPageSet );
		if ( !$res ) {
			return;
		}

		$prop = array_flip( (array)$params['prop'] );
		$this->formatResult( $res, $params['limit'], $prop, $resultPageSet );
	}

	/**
	 * @param object $row
	 *
	 * @return array
	 */
	private function addPageData( $row ) {
		$pageData = [];
		$title = Title::makeTitle( $row->page_namespace, $row->page_title );
		self::addTitleInfo( $pageData, $title );
		$pageData['pageid'] = (int)$row->page_id;
		return $pageData;
	}

	/**
	 * @param IResultWrapper $res
	 * @param int $limit
	 * @param array $prop
	 * @param ApiPageSet|null $resultPageSet
	 */
	private function formatResult(
		IResultWrapper $res,
		$limit,
		array $prop,
		ApiPageSet $resultPageSet = null
	) {
		$currentPageId = null;
		$entry = [];
		$count = 0;
		$result = $this->getResult();
		$prRow = null;

		foreach ( $res as $row ) {
			if ( ++$count > $limit ) {
				// We've reached the one extra which shows that
				// there are additional pages to be had. Stop here...
				$this->setContinueFromRow( $row );
				break;
			}

			if ( $resultPageSet !== null ) {
				$resultPageSet->processDbRow( $row );
			}

			if ( $currentPageId !== null && $row->eu_page_id !== $currentPageId ) {
				// Let's add the data and check if it needs continuation
				$fit = $this->formatPageData( $prRow, $currentPageId, $entry, $result );
				if ( !$fit ) {
					$this->setContinueFromRow( $row );
					break;
				}
				$entry = [];
			}

			$currentPageId = $row->eu_page_id;
			$prRow = $row;

			if ( array_key_exists( $row->eu_entity_id, $entry ) ) {
				$entry[$row->eu_entity_id]['aspects'][] = $row->eu_aspect;
			} else {
				$this->buildEntry( $entry, $row, isset( $prop['url'] ) );
			}

		}
		if ( $entry ) {
			// @phan-suppress-next-line PhanPossiblyUndeclaredVariable
			$this->formatPageData( $row, $currentPageId, $entry, $result );
		}
	}

	/**
	 * @param array $entry
	 * @param object $row
	 * @param bool $url
	 */
	private function buildEntry( &$entry, $row, $url ) {
		$entry[$row->eu_entity_id] = [ 'aspects' => [ $row->eu_aspect ] ];
		if ( $url ) {
			$entry[$row->eu_entity_id]['url'] = $this->repoLinker->getPageUrl(
				'Special:EntityPage/' . $row->eu_entity_id );
		}
		ApiResult::setIndexedTagName(
			$entry[$row->eu_entity_id]['aspects'], 'aspect'
		);
		ApiResult::setArrayType( $entry, 'kvp', 'id' );
	}

	/**
	 * @param object $row
	 * @param int|string $pageId
	 * @param array $entry
	 * @param object $result
	 *
	 * @return bool
	 */
	private function formatPageData( $row, $pageId, array $entry, $result ) {
		$pageData = $this->addPageData( $row );
		$result->addValue( [ 'query', 'pages' ], intval( $pageId ), $pageData );
		$fit = $this->addPageSubItems( $pageId, $entry );
		return $fit;
	}

	/**
	 * @param object $row
	 */
	private function setContinueFromRow( $row ) {
		$this->setContinueEnumParameter(
			'continue',
			"{$row->eu_page_id}|{$row->eu_entity_id}|{$row->eu_aspect}"
		);
	}

	/**
	 * @see ApiQueryBase::getCacheMode
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public function getCacheMode( $params ) {
		return 'public';
	}

	/**
	 * @param array $params
	 * @param ApiPageSet|null $resultPageSet
	 *
	 * @return IResultWrapper|null
	 */
	public function doQuery( array $params, ApiPageSet $resultPageSet = null ) {
		if ( !$params['entities'] ) {
			return null;
		}

		$this->addFields( [
			'eu_page_id',
			'eu_entity_id',
			'eu_aspect'
		] );

		$this->addTables( 'wbc_entity_usage' );

		if ( $resultPageSet === null ) {
			$this->addFields( [ 'page_id', 'page_title', 'page_namespace' ] );
		} else {
			$this->addFields( $resultPageSet->getPageTableFields() );
		}

		$this->addTables( [ 'page' ] );
		$this->addJoinConds( [ 'wbc_entity_usage' => [ 'LEFT JOIN', 'eu_page_id=page_id' ] ] );

		$this->addWhereFld( 'eu_entity_id', $params['entities'] );

		if ( $params['continue'] !== null ) {
			$this->addContinue( $params['continue'] );
		}

		$orderBy = [ 'eu_page_id' , 'eu_entity_id' ];
		if ( isset( $params['aspect'] ) ) {
			$this->addWhereFld( 'eu_aspect', $params['aspect'] );
		} else {
			$orderBy[] = 'eu_aspect';
		}
		$this->addOption( 'ORDER BY', $orderBy );

		$this->addOption( 'LIMIT', $params['limit'] + 1 );
		$res = $this->select( __METHOD__ );
		return $res;
	}

	/**
	 * @param string $continueParam
	 */
	private function addContinue( $continueParam ) {
		$db = $this->getDB();
		list( $pageContinueSql, $entityContinueSql, $aspectContinueSql ) = explode( '|', $continueParam, 3 );
		$pageContinueSql = (int)$pageContinueSql;
		$entityContinueSql = $db->addQuotes( $entityContinueSql );
		$aspectContinueSql = $db->addQuotes( $aspectContinueSql );
		// Filtering out results that have been shown already and
		// starting the query from where it ended.
		$this->addWhere(
			"eu_page_id > $pageContinueSql OR " .
			"(eu_page_id = $pageContinueSql AND " .
			"(eu_entity_id > $entityContinueSql OR " .
			"(eu_entity_id = $entityContinueSql AND " .
			"eu_aspect >= $aspectContinueSql)))"
		);
	}

	public function getAllowedParams() {
		return [
			'prop' => [
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => [
					'url',
				],
				ApiBase::PARAM_HELP_MSG_PER_VALUE => [],
			],
			'aspect' => [
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => [
					EntityUsage::SITELINK_USAGE,
					EntityUsage::LABEL_USAGE,
					EntityUsage::DESCRIPTION_USAGE,
					EntityUsage::TITLE_USAGE,
					EntityUsage::STATEMENT_USAGE,
					EntityUsage::ALL_USAGE,
					EntityUsage::OTHER_USAGE,
				],
				// This reuses the message from the ApiPropsEntityUsage module to avoid needless duplication
				ApiBase::PARAM_HELP_MSG_PER_VALUE => [
					EntityUsage::SITELINK_USAGE => 'apihelp-query+wbentityusage-paramvalue-aspect-S',
					EntityUsage::LABEL_USAGE => 'apihelp-query+wbentityusage-paramvalue-aspect-L',
					EntityUsage::DESCRIPTION_USAGE => 'apihelp-query+wbentityusage-paramvalue-aspect-D',
					EntityUsage::TITLE_USAGE => 'apihelp-query+wbentityusage-paramvalue-aspect-T',
					EntityUsage::STATEMENT_USAGE => 'apihelp-query+wbentityusage-paramvalue-aspect-C',
					EntityUsage::ALL_USAGE => 'apihelp-query+wbentityusage-paramvalue-aspect-X',
					EntityUsage::OTHER_USAGE => 'apihelp-query+wbentityusage-paramvalue-aspect-O',
				],
			],
			'entities' => [
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_REQUIRED => true,
			],
			'limit' => [
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			],
			'continue' => [
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=query&list=wblistentityusage&wbeuentities=Q2'
				=> 'apihelp-query+wblistentityusage-example-simple',
			'action=query&list=wblistentityusage&wbeuentities=Q2&wbeuprop=url'
				=> 'apihelp-query+wblistentityusage-example-url',
			'action=query&list=wblistentityusage&wbeuentities=Q2&wbeuaspect=S|O'
				=> 'apihelp-query+wblistentityusage-example-aspect',
		];
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Special:MyLanguage/Wikibase/API';
	}

}
