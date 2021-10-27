<?php
namespace Wikibase\Search\Elastic;

use CirrusSearch\Search\BaseCirrusSearchResultSet;
use CirrusSearch\Search\BaseResultsType;
use CirrusSearch\Searcher;
use Wikibase\Lib\TermLanguageFallbackChain;

/**
 * Result class for fulltext search of entities.
 */
class EntityResultType extends BaseResultsType {

	/**
	 * Display fallback chain.
	 * @var TermLanguageFallbackChain
	 */
	private $termFallbackChain;
	/**
	 * Display language code
	 * @var string
	 */
	private $displayLanguage;

	/**
	 * @param string $displayLanguage Display Language code
	 * @param TermLanguageFallbackChain $displayFallbackChain Fallback chain for display
	 */
	public function __construct( $displayLanguage, TermLanguageFallbackChain $displayFallbackChain ) {
		$this->termFallbackChain = $displayFallbackChain;
		$this->displayLanguage = $displayLanguage;
	}

	/**
	 * Get the source filtering to be used loading the result.
	 *
	 * @return false|string|array corresponding to Elasticsearch source filtering syntax
	 */
	public function getSourceFiltering() {
		$fields = parent::getSourceFiltering();
		$fields[] = 'timestamp';
		$fields[] = 'sitelink_count';
		$fields[] = 'statement_count';
		foreach ( $this->termFallbackChain->getFetchLanguageCodes() as $code ) {
			$fields[] = "labels.$code";
			$fields[] = "descriptions.$code";
		}
		return $fields;
	}

	/**
	 * Get the fields to load.  Most of the time we'll use source filtering instead but
	 * some fields aren't part of the source.
	 *
	 * @return array corresponding to Elasticsearch fields syntax
	 */
	public function getStoredFields() {
		return [];
	}

	/**
	 * Get the highlighting configuration.
	 *
	 * @param array $highlightSource configuration for how to highlight the source.
	 *  Empty if source should be ignored.
	 * @return array|null highlighting configuration for elasticsearch
	 */
	public function getHighlightingConfiguration( array $highlightSource ) {
		$config = [
			'pre_tags' => [ Searcher::HIGHLIGHT_PRE_MARKER ],
			'post_tags' => [ Searcher::HIGHLIGHT_POST_MARKER ],
			'fields' => [],
		];

		$config['fields']['title'] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 0,
			'matched_fields' => [ 'title.keyword' ]
		];

		foreach ( $this->termFallbackChain->getFetchLanguageCodes() as $code ) {
			$config['fields']["labels.{$code}.plain"] = [
				'type' => 'experimental',
				'fragmenter' => "none",
				'number_of_fragments' => 0,
				'options' => [
					'skip_if_last_matched' => true,
					'return_snippets_and_offsets' => true
				],
			];
			$config['fields']["descriptions.{$code}.plain"] = [
				'type' => 'experimental',
				'fragmenter' => "none",
				'number_of_fragments' => 0,
				'options' => [
					'skip_if_last_matched' => true,
				],
			];
		}

		$config['fields']["labels.*.plain"] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 0,
			'options' => [
				'skip_if_last_matched' => true,
				'return_snippets_and_offsets' => true
			],
		];
		$config['fields']["descriptions.*.plain"] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 0,
			'options' => [
				'skip_if_last_matched' => true,
			],
		];

		return $config;
	}

	/**
	 * @param \Elastica\ResultSet $result
	 * @return mixed Set of search results, the types of which vary by implementation.
	 */
	public function transformElasticsearchResult( \Elastica\ResultSet $result ) {
		return new EntityResultSet( $this->displayLanguage, $this->termFallbackChain, $result );
	}

	/**
	 * @return mixed Empty set of search results
	 */
	public function createEmptyResult() {
		return BaseCirrusSearchResultSet::emptyResultSet( false );
	}

}
