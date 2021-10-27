<?php

namespace Wikibase\Search\Elastic;

use CirrusSearch\CirrusDebugOptions;
use CirrusSearch\Search\SearchContext;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\DisMax;
use Elastica\Query\Match;
use Elastica\Query\Term;
use Language;
use WebRequest;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\Lib\Interactors\TermSearchResult;
use Wikibase\Lib\LanguageFallbackChainFactory;
use Wikibase\Repo\Api\EntitySearchHelper;

/**
 * Entity search implementation using ElasticSearch.
 * Requires CirrusSearch extension and $wgEntitySearchUseCirrus to be on.
 *
 * @license GPL-2.0-or-later
 * @author Stas Malyshev
 */
class EntitySearchElastic implements EntitySearchHelper {
	/**
	 * Default rescore profile
	 */
	public const DEFAULT_RESCORE_PROFILE = 'wikibase_prefix';

	/**
	 * Name of the context for profile name resolution
	 */
	public const CONTEXT_WIKIBASE_PREFIX = 'wikibase_prefix_search';

	/**
	 * Name of the context for profile name resolution
	 */
	public const CONTEXT_WIKIBASE_FULLTEXT = 'wikibase_fulltext_search';

	/**
	 * Name of the profile type used to build the elastic query
	 */
	public const WIKIBASE_PREFIX_QUERY_BUILDER = 'wikibase_prefix_querybuilder';

	/**
	 * Default query builder profile for prefix searches
	 */
	public const DEFAULT_QUERY_BUILDER_PROFILE = 'default';

	/**
	 * Default query builder profile for fulltext searches
	 *
	 */
	public const DEFAULT_FULL_TEXT_QUERY_BUILDER_PROFILE = 'wikibase';

	/**
	 * Replacement syntax for statement boosting
	 * @see \CirrusSearch\Profile\SearchProfileRepositoryTransformer
	 * and repo/config/ElasticSearchRescoreFunctions.php
	 */
	public const STMT_BOOST_PROFILE_REPL = 'functions.*[type=term_boost].params[statement_keywords=_statementBoost_].statement_keywords';

	/**
	 * @var LanguageFallbackChainFactory
	 */
	private $languageChainFactory;

	/**
	 * @var EntityIdParser
	 */
	private $idParser;

	/**
	 * @var string[]
	 */
	private $contentModelMap;

	/**
	 * Web request context.
	 * Used for implementing debug features such as cirrusDumpQuery.
	 * @var \WebRequest
	 */
	private $request;

	/**
	 * List of fallback codes for search language
	 * @var string[]
	 */
	private $searchLanguageCodes = [];

	/**
	 * @var Language User language for display.
	 */
	private $userLang;

	/**
	 * @var CirrusDebugOptions
	 */
	private $debugOptions;

	/**
	 * @param LanguageFallbackChainFactory $languageChainFactory
	 * @param EntityIdParser $idParser
	 * @param Language $userLang
	 * @param array $contentModelMap Maps entity type => content model name
	 * @param WebRequest|null $request Web request context
	 * @param CirrusDebugOptions|null $options
	 * @throws \MWException
	 */
	public function __construct(
		LanguageFallbackChainFactory $languageChainFactory,
		EntityIdParser $idParser,
		Language $userLang,
		array $contentModelMap,
		WebRequest $request = null,
		CirrusDebugOptions $options = null
	) {
		$this->languageChainFactory = $languageChainFactory;
		$this->idParser = $idParser;
		$this->userLang = $userLang;
		$this->contentModelMap = $contentModelMap;
		$this->request = $request ?: new \FauxRequest();
		$this->debugOptions = $options ?: CirrusDebugOptions::fromRequest( $this->request );
	}

	private function expandGenericProfile( $languageCode, array $profile ) {
		$res = [
			'language-chain' => $this->languageChainFactory
				->newFromLanguageCode( $languageCode )
				->getFetchLanguageCodes(),
			'any' => $profile['any'],
			'tie-breaker' => $profile['tie-breaker'],
			'space-discount' => $profile['space-discount'] ?? null,
			"{$languageCode}-exact" => $profile['lang-exact'],
			"{$languageCode}-folded" => $profile['lang-folded'],
			"{$languageCode}-prefix" => $profile['lang-prefix'],
		];

		$discount = $profile['fallback-discount'];
		foreach ( $res['language-chain'] as $fallback ) {
			if ( $fallback === $languageCode ) {
				continue;
			}
			$res["{$fallback}-exact"] = $profile['fallback-exact'] * $discount;
			$res["{$fallback}-folded"] = $profile['fallback-folded'] * $discount;
			$res["{$fallback}-prefix"] = $profile['fallback-prefix'] * $discount;
			$discount *= $profile['fallback-discount'];
		}

		return $res;
	}

	private function loadProfile( SearchContext $context, $languageCode ) {
		$profile = $context->getConfig()
			->getProfileService()
			->loadProfile( self::WIKIBASE_PREFIX_QUERY_BUILDER, self::CONTEXT_WIKIBASE_PREFIX, null, [
				'language' => $languageCode ] );

		// Set some bc defaults for properties that didn't always exist.
		$profile['tie-breaker'] = $profile['tie-breaker'] ?? 0;

		// There are two flavors of profiles: fully specified, and generic
		// fallback. When language-chain is provided we assume a fully
		// specified profile. Otherwise we expand the language agnostic
		// profile into a language specific profile.
		if ( !isset( $profile['language-chain'] ) ) {
			$profile = $this->expandGenericProfile( $languageCode, $profile );
		}

		return $profile;
	}

	/**
	 * Produce ES query that matches the arguments.
	 *
	 * @param string $text
	 * @param string $languageCode
	 * @param string $entityType
	 * @param bool $strictLanguage
	 * @param SearchContext $context
	 *
	 * @return AbstractQuery
	 */
	protected function getElasticSearchQuery(
		$text,
		$languageCode,
		$entityType,
		$strictLanguage,
		SearchContext $context
	) {
		$query = new BoolQuery();

		$context->setOriginalSearchTerm( $text );
		// Drop only leading spaces for exact matches, and all spaces for the rest
		$textExact = ltrim( $text );
		$text = trim( $text );
		if ( empty( $this->contentModelMap[$entityType] ) ) {
			$context->setResultsPossible( false );
			$context->addWarning( 'wikibasecirrus-search-bad-entity-type', $entityType );
			return $query;
		}

		$labelsFilter = new Match( 'labels_all.prefix', $text );

		$profile = $this->loadProfile( $context, $languageCode );
		$this->searchLanguageCodes = $profile['language-chain'];
		if ( $languageCode !== $this->searchLanguageCodes[0] ) {
			// Log a warning? Are there valid reasons for the primary language
			// in the profile to not match the profile request?
			$languageCode = $this->searchLanguageCodes[0];
		}

		$fields = [
			[ "labels.{$languageCode}.near_match", $profile["{$languageCode}-exact"] ],
			[ "labels.{$languageCode}.near_match_folded", $profile["{$languageCode}-folded"] ],
		];
		// Fields to which query applies exactly as stated, without trailing space trimming
		$fieldsExact = [];
		$weight = $profile["{$languageCode}-prefix"];
		if ( $textExact !== $text && isset( $profile['space-discount'] ) ) {
			$fields[] =
				[
					"labels.{$languageCode}.prefix",
					$weight * $profile['space-discount'],
				];
			$fieldsExact[] = [ "labels.{$languageCode}.prefix", $weight ];
		} else {
			$fields[] = [ "labels.{$languageCode}.prefix", $weight ];
		}

		if ( !$strictLanguage ) {
			$fields[] = [ "labels_all.near_match_folded", $profile['any'] ];
			foreach ( $this->searchLanguageCodes as $fallbackCode ) {
				if ( $fallbackCode === $languageCode ) {
					continue;
				}
				$fields[] = [
					"labels.{$fallbackCode}.near_match",
					$profile["{$fallbackCode}-exact"] ];
				$fields[] = [
					"labels.{$fallbackCode}.near_match_folded",
					$profile["{$fallbackCode}-folded"] ];

				$weight = $profile["{$fallbackCode}-prefix"];
				if ( $textExact !== $text && isset( $profile['space-discount'] ) ) {
					$fields[] = [
						"labels.{$fallbackCode}.prefix",
						$weight * $profile['space-discount']
					];
					$fieldsExact[] = [ "labels.{$fallbackCode}.prefix", $weight ];
				} else {
					$fields[] = [ "labels.{$fallbackCode}.prefix", $weight ];
				}
			}
		}

		$dismax = new DisMax();
		$dismax->setTieBreaker( $profile['tie-breaker'] );
		foreach ( $fields as $field ) {
			$dismax->addQuery( EntitySearchUtils::makeConstScoreQuery( $field[0], $field[1], $text ) );
		}

		foreach ( $fieldsExact as $field ) {
			$dismax->addQuery( EntitySearchUtils::makeConstScoreQuery( $field[0], $field[1], $textExact ) );
		}

		$labelsQuery = new BoolQuery();
		$labelsQuery->addFilter( $labelsFilter );
		$labelsQuery->addShould( $dismax );
		$titleMatch = new Term( [ 'title.keyword' => EntitySearchUtils::normalizeId( $text, $this->idParser ) ] );

		// Match either labels or exact match to title
		$query->addShould( $labelsQuery );
		$query->addShould( $titleMatch );
		$query->setMinimumShouldMatch( 1 );

		// Filter to fetch only given entity type
		$query->addFilter( new Term( [ 'content_model' => $this->contentModelMap[$entityType] ] ) );

		return $query;
	}

	/**
	 * @param string $text
	 * @param string $languageCode
	 * @param string $entityType
	 * @param int $limit
	 * @param bool $strictLanguage
	 *
	 * @return TermSearchResult[]
	 */
	public function getRankedSearchResults(
		$text,
		$languageCode,
		$entityType,
		$limit,
		$strictLanguage
	) {
		$searcher = new WikibasePrefixSearcher( 0, $limit, $this->debugOptions );
		$query = $this->getElasticSearchQuery( $text, $languageCode, $entityType, $strictLanguage,
				$searcher->getSearchContext() );

		$searcher->setResultsType( new ElasticTermResult(
			$this->idParser,
			$this->searchLanguageCodes,
			$this->languageChainFactory->newFromLanguage( $this->userLang )
		) );

		$searcher->getSearchContext()->setProfileContext(
			self::CONTEXT_WIKIBASE_PREFIX,
			[ 'language' => $languageCode ] );
		$result = $searcher->performSearch( $query );

		if ( $result->isOK() ) {
			$result = $result->getValue();
		} else {
			// FIXME: $result->getErrors() contains error messages for the
			// end user, but we don't have any way to pass them on.
			$result = [];
		}

		if ( $searcher->isReturnRaw() ) {
			$result = $searcher->processRawReturn( $result, $this->request );
		}

		return $result;
	}

	/**
	 * Determine from the classpath which elastic version we
	 * aim to be compatible with.
	 * @return int
	 */
	public static function getExpectedElasticMajorVersion() {
		if ( class_exists( '\Elastica\Task' ) ) {
			return 6;
		}

		return 5;
	}

}
