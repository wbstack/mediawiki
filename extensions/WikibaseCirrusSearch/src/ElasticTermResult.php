<?php

namespace Wikibase\Search\Elastic;

use CirrusSearch\Search\BaseResultsType;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Term\Term;
use Wikibase\Lib\Interactors\TermSearchResult;
use Wikibase\Lib\TermLanguageFallbackChain;

/**
 * This result type implements the result for searching
 * a Wikibase entity by its label or alias.
 *
 * @license GPL-2.0-or-later
 * @author Stas Malyshev
 */
class ElasticTermResult extends BaseResultsType {

	/**
	 * @var EntityIdParser
	 */
	private $idParser;

	/**
	 * List of language codes in the search fallback chain, the first
	 * is the preferred language.
	 * @var string[]
	 */
	private $searchLanguageCodes;

	/**
	 * Display fallback chain.
	 * @var TermLanguageFallbackChain
	 */
	private $termFallbackChain;

	/**
	 * @param EntityIdParser $idParser
	 * @param string[] $searchLanguageCodes Language fallback chain for search
	 * @param TermLanguageFallbackChain $displayFallbackChain Fallback chain for display
	 */
	public function __construct(
		EntityIdParser $idParser,
		array $searchLanguageCodes,
		TermLanguageFallbackChain $displayFallbackChain
	) {
		$this->idParser = $idParser;
		$this->searchLanguageCodes = $searchLanguageCodes;
		$this->termFallbackChain = $displayFallbackChain;
	}

	/**
	 * Get the source filtering to be used loading the result.
	 *
	 * @return string[]
	 */
	public function getSourceFiltering() {
		$fields = parent::getSourceFiltering();
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
	 * @return string[]
	 */
	public function getFields() {
		return [];
	}

	/**
	 * ES5 variant of getFields.
	 * @return string[]
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
			'pre_tags' => [ '' ],
			'post_tags' => [ '' ],
			'fields' => [],
		];
		$config['fields']['title'] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 0,
			'matched_fields' => [ 'title.keyword' ]
		];
		foreach ( $this->searchLanguageCodes as $code ) {
			$config['fields']["labels.$code.prefix"] = [
				'type' => 'experimental',
				'fragmenter' => "none",
				'number_of_fragments' => 0,
				'options' => [
					'skip_if_last_matched' => true,
					'return_snippets_and_offsets' => true
				],
			];
		}
		$config['fields']['labels.*.prefix'] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 0,
			'options' => [
				'skip_if_last_matched' => true,
				'return_snippets_and_offsets' => true
			],
		];

		return $config;
	}

	/**
	 * Convert search result from ElasticSearch result set to TermSearchResult.
	 * @param \Elastica\ResultSet $result
	 * @return TermSearchResult[] Set of search results, the types of which vary by implementation.
	 */
	public function transformElasticsearchResult( \Elastica\ResultSet $result ) {
		$results = [];
		foreach ( $result->getResults() as $r ) {
			$sourceData = $r->getSource();
			try {
				$entityId = $this->idParser->parse( $sourceData['title'] );
			} catch ( EntityIdParsingException $e ) {
				// Can not parse entity ID - skip it
				continue;
			}

			// Highlight part contains information about what has actually been matched.
			$highlight = $r->getHighlights();
			$displayLabel = EntitySearchUtils::findTermForDisplay( $sourceData, 'labels', $this->termFallbackChain );
			$displayDescription = EntitySearchUtils::findTermForDisplay( $sourceData, 'descriptions', $this->termFallbackChain );

			if ( !empty( $highlight['title'] ) ) {
				// If we matched title, this means it's a match by ID
				$matchedTermType = 'entityId';
				$matchedTerm = new Term( 'qid', $sourceData['title'] );
			} elseif ( empty( $highlight ) ) {
				// Something went wrong, we don't have any highlighting data
				continue;
			} else {
				list( $matchedTermType, $langCode, $term ) =
					$this->extractTermFromHighlight( $highlight, $sourceData );
				$matchedTerm = new Term( $langCode, $term );
			}

			if ( !$displayLabel ) {
				// This should not happen, but just in case, it's better to return something
				$displayLabel = $matchedTerm;
			}

			$results[$entityId->getSerialization()] = new TermSearchResult(
				$matchedTerm, $matchedTermType, $entityId, $displayLabel,
				$displayDescription
			);
		}

		return $results;
	}

	/**
	 * New highlighter pattern.
	 * The new highlighter can return offsets as: 1:1-XX:YY|Text Snippet
	 * or even SNIPPET_START:MATCH1_START-MATCH1_END,MATCH2_START-MATCH2_END,...:SNIPPET_END|Text
	 */
	public const HIGHLIGHT_PATTERN = '/^\d+:\d+-\d+(?:,\d+-\d+)*:\d+\|(.+)/';

	/**
	 * Extract term, language and type from highlighter results.
	 * @param array $highlight Data from highlighter
	 * @param array[] $sourceData Data from _source
	 * @return array Array of: [string $termType, string $languageCode, string $term]
	 */
	private function extractTermFromHighlight( array $highlight, array $sourceData ) {
		/**
		 * Highlighter returns:
		 * {
		 *   labels.en.prefix: [
		 *	  "metre"  // or "0:0-5:5|metre"
		 *   ]
		 * }
		 */
		$matchedTermType = 'label';
		$term = reset( $highlight ); // Take the first one
		$term = $term[0]; // Highlighter returns array
		$field = key( $highlight );
		if ( preg_match( '/^labels\.([^.]+)\.prefix$/', $field, $match ) ) {
			$langCode = $match[1];
			if ( preg_match( self::HIGHLIGHT_PATTERN, $term, $termMatch ) ) {
				$isFirst = ( $term[0] === '0' );
				$term = $termMatch[1];
			} else {
				$isFirst = true;
			}
			if ( !empty( $sourceData['labels'][$langCode] ) ) {
				// Here we have match in one of the languages we asked for.
				// Primary label always comes first, so if it's not the first one,
				// it's an alias.
				if ( $sourceData['labels'][$langCode][0] !== $term ) {
					$matchedTermType = 'alias';
				}
			} else {
				// Here we have match in one of the "other" languages.
				// If it's the first one in the list, it's label, otherwise it is alias.
				$matchedTermType = $isFirst ? 'label' : 'alias';
			}
		} else {
			// This is weird since we didn't ask to match anything else,
			// but we'll return it anyway for debugging.
			$langCode = 'unknown';
		}
		return [ $matchedTermType, $langCode, $term ];
	}

	/**
	 * @return TermSearchResult[] Empty set of search results
	 */
	public function createEmptyResult() {
		return [];
	}

}
