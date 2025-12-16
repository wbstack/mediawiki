<?php
namespace Wikibase\Lexeme\Search\Elastic;

use CirrusSearch\Search\BaseCirrusSearchResultSet;
use CirrusSearch\Search\BaseResultsType;
use Elastica\ResultSet;
use MediaWiki\Language\Language;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Lexeme\DataAccess\LexemeDescription;
use Wikibase\Lib\Store\FallbackLabelDescriptionLookupFactory;
use Wikibase\Search\Elastic\EntitySearchUtils;
use Wikibase\Search\Elastic\Fields\StatementCountField;

/**
 * This result type implements the result for searching a Lexeme for fulltext search.
 *
 * @license GPL-2.0-or-later
 * @author Stas Malyshev
 */
class LexemeFulltextResult extends BaseResultsType {

	/**
	 * @var EntityIdParser
	 */
	private $idParser;

	/**
	 * Display language
	 * @var Language
	 */
	private $displayLanguage;
	/**
	 * @var FallbackLabelDescriptionLookupFactory
	 */
	private $termLookupFactory;

	/**
	 * @param EntityIdParser $idParser
	 * @param Language $displayLanguage User display language
	 * @param FallbackLabelDescriptionLookupFactory $termLookupFactory
	 *        Lookup factory for assembling descriptions
	 */
	public function __construct(
		EntityIdParser $idParser,
		Language $displayLanguage,
		FallbackLabelDescriptionLookupFactory $termLookupFactory
	) {
		$this->idParser = $idParser;
		$this->termLookupFactory = $termLookupFactory;
		$this->displayLanguage = $displayLanguage;
	}

	/**
	 * Get the source filtering to be used loading the result.
	 *
	 * @return string[]
	 */
	public function getSourceFiltering() {
		return array_merge( parent::getSourceFiltering(), [
				LemmaField::NAME,
				LexemeLanguageField::NAME,
				LexemeCategoryField::NAME,
				FormsField::NAME,
				StatementCountField::NAME,
				// The web ui for fulltext search expects this to be returned.
				// Longer term there should probably be some concept where the UI
				// requests additional properties instead of baking it in at these
				// lower levels for each fulltext results type.
				'timestamp',
		] );
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
		$config['fields']['lexeme_forms.id'] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 0,
			'options' => [
				'skip_if_last_matched' => true,
			],
		];
		$config['fields']["lemma"] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 0,
			'options' => [
				'skip_if_last_matched' => true,
			],
		];
		$config['fields']["lexeme_forms.representation"] = [
			'type' => 'experimental',
			'fragmenter' => "none",
			'number_of_fragments' => 30,
			'fragment_size' => 1000, // Hopefully this is enough
			'options' => [
				'skip_if_last_matched' => true,
			],
		];

		return $config;
	}

	/**
	 * Produce raw result for Form ID match.
	 * @param string[][] $highlight Highlighter data
	 * @param array $sourceData Lexeme source data
	 * @return array|null Null if match is bad
	 */
	private function getFormIdResult( $highlight, $sourceData ) {
		$formId = $highlight['lexeme_forms.id'][0];
		$formIdParsed = EntitySearchUtils::parseOrNull( $formId, $this->idParser );
		if ( !$formIdParsed ) {
			// Got some bad id?? Weird.
			return null;
		}
		$repr = '';
		$features = [];
		foreach ( $sourceData['lexeme_forms'] as $form ) {
			if ( $form['id'] === $formId ) {
				// TODO: how we choose one?
				$repr = $form['representation'][0];
				// Convert features to EntityId's
				$features = array_filter( array_map( function ( $featureId ) {
					return EntitySearchUtils::parseOrNull( $featureId, $this->idParser );
				}, $form['features'] ) );
				break;
			}
		}
		if ( $repr === '' ) {
			// Didn't find the right id? Weird, skip it.
			return null;
		}

		return [
			'formId' => $formId,
			'representation' => $repr,
			'features' => $features,
		];
	}

	/**
	 * Get data for specific form match from source data
	 * @param array[] $sourceForms 'forms' field of the source data
	 * @param string[] $highlight Highlighter data about match
	 * @return array|null Null if match is bad
	 */
	private function getFormRepresentationResult( $sourceForms, $highlight ) {
		foreach ( $sourceForms as $form ) {
			$reprMatches = array_intersect( $form['representation'],
				$highlight );
			if ( !$reprMatches ) {
				continue;
			}
			// matches the data
			$formIdParsed = EntitySearchUtils::parseOrNull( $form['id'], $this->idParser );
			if ( !$formIdParsed ) {
				// Got some bad id?? Weird.
				continue;
			}
			// Convert features to EntityId's
			$featureIds = array_filter( array_map( function ( $featureId ) {
				return EntitySearchUtils::parseOrNull( $featureId, $this->idParser );
			}, $form['features'] ) );

			return [
				'formId' => $formIdParsed,
				'representation' => reset( $reprMatches ),
				'features' => $featureIds,
			];
		}
		// Didn't find anything
		return null;
	}

	/**
	 * Convert search result from ElasticSearch result set to LexemeResultSet.
	 *
	 * The data inside the set are not rendered yet, but the set is configured with
	 * the label lookup that has necessary item labels already loaded.
	 *
	 * @param ResultSet $result ElasticSearch results
	 * @return \ISearchResultSet
	 */
	public function transformElasticsearchResult( ResultSet $result ) {
		$rawResults = $entityIds = [];
		foreach ( $result->getResults() as $r ) {
			$rawResultKey = spl_object_hash( $r );
			$sourceData = $r->getSource();
			$entityId = EntitySearchUtils::parseOrNull( $sourceData['title'], $this->idParser );
			if ( !$entityId ) {
				// Can not parse entity ID - skip it
				// TODO: what we do here if no language code?
				// Not sure we want to index all lemma languages.
				// Should we just fake the term language code?
				continue;
			}

			$lemmaCode = LexemeTermResult::extractLanguageCode( $sourceData );

			// Highlight part contains information about what has actually been matched.
			$highlight = $r->getHighlights();

			// we accept missing lemma fields (see T365692)
			$lang = $sourceData['lexeme_language']['entity'] ?? '';
			$category = $sourceData['lexical_category'] ?? '';

			$features = [];
			$lexemeData = [
				'lexemeId' => $entityId,
				// Having empty lemma is unusual, but in theory possible
				'lemma' => empty( $sourceData['lemma'] ) ? '' : $sourceData['lemma'][0],
				'lang' => $lang,
				'langcode' => $lemmaCode,
				'category' => $category,
				'elasticResult' => $r
			];

			if ( !empty( $highlight['lexeme_forms.id'] ) ) {
				// If we matched Form ID, this means it's a match by ID

				$idResult = $this->getFormIdResult( $highlight, $sourceData );
				if ( !$idResult ) {
					continue;
				}

				$lexemeData = $idResult + $lexemeData;
				$features = array_merge( $features, $idResult['features'] );
			} elseif ( !empty( $highlight['lemma'] ) ) {
				// TODO: make result display highlight this
				$lexemeData['matchedLemma'] = $highlight['lemma'][0];
			} elseif ( !empty( $highlight["lexeme_forms.representation"] ) ) {
				// For now, find the first form that matches
				$formResult = $this->getFormRepresentationResult( $sourceData['lexeme_forms'],
						$highlight['lexeme_forms.representation'] );
				if ( $formResult ) {
					$lexemeData = $formResult + $lexemeData;
					$features = array_merge( $features, $formResult['features'] );
				}
			}

			// Doing two-stage resolution here since we want to prefetch all labels for
			// auxiliary entities before using them to construct descriptions.
			$lexemeData['elastica_result_hash'] = $rawResultKey;
			$rawResults[$entityId->getSerialization()] = $lexemeData;
			$entityIds[$lang] = EntitySearchUtils::parseOrNull( $lang, $this->idParser );
			$entityIds[$category] = EntitySearchUtils::parseOrNull( $category, $this->idParser );
			foreach ( $features as $feature ) {
				$entityIds[$feature->getSerialization()] = $feature;
			}
		}

		if ( !$rawResults ) {
			return new \CirrusSearch\Search\ResultSet();
		}
		// Create prefetched lookup
		$termLookup = $this->termLookupFactory->newLabelDescriptionLookup( $this->displayLanguage,
			array_filter( $entityIds ) );
		$descriptionMaker = new LexemeDescription( $termLookup, $this->idParser,
			$this->displayLanguage );

		return new LexemeResultSet( $result, $this->displayLanguage, $descriptionMaker, $rawResults );
	}

	/**
	 * @return mixed Empty set of search results
	 */
	public function createEmptyResult() {
		return BaseCirrusSearchResultSet::emptyResultSet( false );
	}

}
