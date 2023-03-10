<?php

namespace Wikibase\Search\Elastic;

use CirrusSearch\Extra\Query\TokenCountRouter;
use CirrusSearch\Query\FullTextQueryBuilder;
use CirrusSearch\Search\SearchContext;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\DisMax;
use Elastica\Query\MatchNone;
use Elastica\Query\MatchQuery;
use Elastica\Query\MultiMatch;
use Elastica\Query\Term;
use MediaWiki\MediaWikiServices;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Lib\LanguageFallbackChainFactory;
use Wikibase\Repo\WikibaseRepo;

/**
 * Builder for entity fulltext queries
 */
class EntityFullTextQueryBuilder implements FullTextQueryBuilder {
	public const ENTITY_FULL_TEXT_MARKER = 'entity_full_text';

	/**
	 * @var array
	 */
	private $settings;
	/**
	 * Repository 'entitySearch' settings
	 * @var array
	 */
	private $stemmingSettings;
	/**
	 * @var LanguageFallbackChainFactory
	 */
	private $languageFallbackChainFactory;
	/**
	 * @var EntityIdParser
	 */
	private $entityIdParser;
	/**
	 * @var string User language code
	 */
	private $userLanguage;

	/**
	 * @param array $stemmingSettings Stemming settings from UseStemming config entry
	 * @param array $settings Settings from EntitySearchProfiles.php
	 * @param LanguageFallbackChainFactory $languageFallbackChainFactory
	 * @param EntityIdParser $entityIdParser
	 * @param string $userLanguage User's language code
	 */
	public function __construct(
		array $stemmingSettings,
		array $settings,
		LanguageFallbackChainFactory $languageFallbackChainFactory,
		EntityIdParser $entityIdParser,
		$userLanguage
	) {
		$this->stemmingSettings = $stemmingSettings;
		$this->settings = $settings;
		$this->languageFallbackChainFactory = $languageFallbackChainFactory;
		$this->entityIdParser = $entityIdParser;
		$this->userLanguage = $userLanguage;
	}

	/**
	 * Create fulltext builder from global environment.
	 * @param array $settings Configuration from config file
	 * @return EntityFullTextQueryBuilder
	 * @throws \MWException
	 */
	public static function newFromGlobals( array $settings ) {
		$services = MediaWikiServices::getInstance();
		$config = $services->getConfigFactory()->makeConfig( 'WikibaseCirrusSearch' );
		return new static(
			$config->get( 'UseStemming' ),
			$settings,
			WikibaseRepo::getLanguageFallbackChainFactory( $services ),
			WikibaseRepo::getEntityIdParser( $services ),
			WikibaseRepo::getUserLanguage( $services )->getCode()
		);
	}

	/**
	 * Search articles with provided term.
	 *
	 * @param SearchContext $searchContext
	 * @param string $term term to search
	 * @throws \MWException
	 */
	public function build( SearchContext $searchContext, $term ) {
		$this->buildEntitySearchQuery( $searchContext, $term );
		// if we did find advanced query, we keep the old setup but change the result type
		// FIXME: make it dispatch by content model
		$searchContext->setResultsType( new EntityResultType( $this->userLanguage,
			$this->languageFallbackChainFactory->newFromLanguageCode( $this->userLanguage ) ) );
	}

	/**
	 * @param SearchContext $searchContext
	 * @return bool
	 */
	public function buildDegraded( SearchContext $searchContext ) {
		// Not doing anything for now
		return false;
	}

	/**
	 * Build a fulltext query for Wikibase entity.
	 * @param SearchContext $searchContext
	 * @param string $term Search term
	 */
	protected function buildEntitySearchQuery( SearchContext $searchContext, $term ) {
		$searchContext->addSyntaxUsed( self::ENTITY_FULL_TEXT_MARKER, 10 );
		/*
		 * Overall query structure is as follows:
		 * - Bool with:
		 *   Filter of namespace = N
		 *   OR (Should with 1 mininmum) of:
		 *     title.keyword = QUERY
		 *     fulltext match query
		 *
		 * Fulltext match query is:
		 *   Filter of:
		 *      at least one of: all, all.plain matching
		 *      description (for stemmed) or description.en (for non-stemmed) matching, with fallback
		 *   OR (should with 0 minimum) of:
		 *     DISMAX query of: all labels.near_match in fallback chain
		 *     OR (should with 0 minimum) of:
		 *        all
		 *        all.plain
		 *        DISMAX of: all fulltext matches for tokenized fields
		 */

		$profile = $this->settings;
		// $fields is collecting all the fields for dismax query to be used in
		// scoring match
		$fields = [
			[ "labels.{$this->userLanguage}.near_match", $profile['lang-exact'] ],
			[ "labels.{$this->userLanguage}.near_match_folded", $profile['lang-folded'] ],
		];

		if ( empty( $this->stemmingSettings[$this->userLanguage]['query'] ) ) {
			$fieldsTokenized = [
				[ "labels.{$this->userLanguage}.plain", $profile['lang-partial'] ],
				[ "descriptions.{$this->userLanguage}.plain", $profile['lang-partial'] ],
			];
		} else {
			$fieldsTokenized = [
				[ "descriptions.{$this->userLanguage}", $profile['lang-partial'] ],
				[ "labels.{$this->userLanguage}.plain", $profile['lang-partial'] ],
				[ "descriptions.{$this->userLanguage}.plain", $profile['lang-partial'] ],
			];
		}

		$searchLanguageCodes = $this->languageFallbackChainFactory->newFromLanguageCode( $this->userLanguage )
				->getFetchLanguageCodes();

		$discount = $profile['fallback-discount'];
		$stemFilterFields = [];

		foreach ( $searchLanguageCodes as $fallbackCode ) {
			if ( empty( $this->stemmingSettings[$fallbackCode]['query'] ) ) {
				$stemFilterFields[] = "descriptions.{$fallbackCode}.plain";
			} else {
				$stemFilterFields[] = "descriptions.{$fallbackCode}";
			}

			if ( $fallbackCode === $this->userLanguage ) {
				continue;
			}

			$weight = $profile['fallback-exact'] * $discount;
			$fields[] = [ "labels.{$fallbackCode}.near_match", $weight ];

			$weight = $profile['fallback-folded'] * $discount;
			$fields[] = [ "labels.{$fallbackCode}.near_match_folded", $weight ];

			$weight = $profile['fallback-partial'] * $discount;
			$fieldsTokenized[] = [ "labels.{$fallbackCode}.plain", $weight ];
			if ( empty( $this->stemmingSettings[$fallbackCode]['query'] ) ) {
				$fieldsTokenized[] = [ "descriptions.{$fallbackCode}.plain", $weight ];
			} else {
				$fieldsTokenized[] = [ "descriptions.{$fallbackCode}", $weight ];
				$fieldsTokenized[] = [ "descriptions.{$fallbackCode}.plain", $weight ];
			}

			$discount *= $profile['fallback-discount'];
		}

		$titleMatch = new Term( [
			'title.keyword' => EntitySearchUtils::normalizeId( $term, $this->entityIdParser ),
		] );

		// Main query filter
		$filterQuery = $this->buildSimpleAllFilter( $term );
		foreach ( $stemFilterFields as $filterField ) {
			$filterQuery->addShould( $this->buildFieldMatch( $filterField, $term, 'AND' ) );
		}

		// Near match ones, they use constant score
		$nearMatchQuery = new DisMax();
		$nearMatchQuery->setTieBreaker( 0 );
		foreach ( $fields as $field ) {
			$nearMatchQuery->addQuery( EntitySearchUtils::makeConstScoreQuery( $field[0], $field[1],
				$term ) );
		}

		// Tokenized ones
		$tokenizedQuery = $this->buildSimpleAllFilter( $term, 'OR', $profile['any'] );
		$tokenizedQueryFields = new DisMax();
		$tokenizedQueryFields->setTieBreaker( 0.2 );
		foreach ( $fieldsTokenized as $field ) {
			$m = $this->buildFieldMatch( $field[0], $term );
			$m->setFieldBoost( $field[0], $field[1] );
			$tokenizedQueryFields->addQuery( $m );
		}
		$tokenizedQuery->addShould( $tokenizedQueryFields );

		// Main labels/desc query
		$labelsDescQuery = new BoolQuery();
		$labelsDescQuery->addFilter( $filterQuery );
		$labelsDescQuery->addShould( $nearMatchQuery );
		$labelsDescQuery->addShould( $tokenizedQuery );

		// Main query
		$query = new BoolQuery();
		$query->setParam( 'disable_coord', true );

		// Match either labels or exact match to title
		$query->addShould( $titleMatch );
		$query->addShould( $labelsDescQuery );
		$query->setMinimumShouldMatch( 1 );

		$searchContext->setMainQuery( $query );
		$searchContext->setPhraseRescoreQuery( $this->buildPhraseRescore( $term, $searchContext, $profile ) );
	}

	/**
	 * Builds a simple filter on all and all.plain when all terms must match
	 *
	 * @param string $query
	 * @param string $operator
	 * @param null $boost
	 * @return BoolQuery
	 */
	private function buildSimpleAllFilter( $query, $operator = 'AND', $boost = null ) {
		$filter = new BoolQuery();
		// FIXME: We can't use solely the stem field here
		// - Depending on languages it may lack stopwords,
		// A dedicated field used for filtering would be nice
		foreach ( [ 'all', 'all.plain' ] as $field ) {
			$m = new MatchQuery();
			$m->setFieldQuery( $field, $query );
			$m->setFieldOperator( $field, $operator );
			if ( $boost ) {
				$m->setFieldBoost( $field, $boost );
			}
			$filter->addShould( $m );
		}
		return $filter;
	}

	/**
	 * Build simple match clause, matching field against term
	 * @param string $field
	 * @param string $term
	 * @param string|null $operator
	 * @return MatchQuery
	 */
	private function buildFieldMatch( $field, $term, $operator = null ) {
		$m = new MatchQuery();
		$m->setFieldQuery( $field, $term );
		if ( $operator ) {
			$m->setFieldOperator( $field, $operator );
		}
		return $m;
	}

	/**
	 * Create phrase rescore query for "all" fields
	 * @param string $queryText
	 * @param SearchContext $context
	 * @param float[][] $profile Must contain $profile['phrase'] with keys 'all', 'slop', 'all.plain'
	 * @return AbstractQuery|null
	 */
	private function buildPhraseRescore( $queryText, SearchContext $context, array $profile ) {
		if ( empty( $profile['phrase'] ) ) {
			return null;
		} else {
			$phraseProfile = $profile['phrase'];
		}
		$useRouter = $context->getConfig()->getElement( 'CirrusSearchWikimediaExtraPlugin', 'token_count_router' ) === true;
		$phrase = new MultiMatch();
		$phrase->setParam( 'type', 'phrase' );
		$phrase->setParam( 'slop', $phraseProfile['slop'] );
		$fields = [
			"all^{$phraseProfile['all']}", "all.plain^{$phraseProfile['all.plain']}"
		];
		$phrase->setFields( $fields );
		$phrase->setQuery( $queryText );
		if ( !$useRouter ) {
			return $phrase;
		}
		$tokCount = new TokenCountRouter(
		// text
			$queryText,
			// fallback
			new MatchNone(),
			// field
			null,
			// analyzer
			'text_search'
		);
		$tokCount->addCondition(
			TokenCountRouter::GT,
			1,
			$phrase
		);
		$maxTokens = $context->getConfig()->get( 'CirrusSearchMaxPhraseTokens' );
		if ( $maxTokens ) {
			$tokCount->addCondition(
				TokenCountRouter::GT,
				$maxTokens,
				new \Elastica\Query\MatchNone()
			);
		}
		return $tokCount;
	}

}
