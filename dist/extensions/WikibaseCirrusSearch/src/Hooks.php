<?php

namespace Wikibase\Search\Elastic;

use CirrusSearch\Maintenance\AnalysisConfigBuilder;
use CirrusSearch\Parser\BasicQueryClassifier;
use CirrusSearch\Profile\ArrayProfileRepository;
use CirrusSearch\Profile\SearchProfileRepositoryTransformer;
use CirrusSearch\Profile\SearchProfileService;
use Language;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Search\Elastic\Fields\StatementsField;
use Wikibase\Search\Elastic\Query\HasDataForLangFeature;
use Wikibase\Search\Elastic\Query\HasLicenseFeature;
use Wikibase\Search\Elastic\Query\HasWbStatementFeature;
use Wikibase\Search\Elastic\Query\InLabelFeature;
use Wikibase\Search\Elastic\Query\WbStatementQuantityFeature;
use Wikimedia\Assert\Assert;

/**
 * Hooks for Wikibase search.
 */
class Hooks {

	/**
	 * Setup hook.
	 * Enables/disables CirrusSearch depending on request settings.
	 */
	public static function onSetupAfterCache() {
		$request = RequestContext::getMain()->getRequest();
		$useCirrus = $request->getVal( 'useCirrus' );
		if ( $useCirrus !== null ) {
			$GLOBALS['wgWBCSUseCirrus'] = wfStringToBool( $useCirrus );
		}
		$config = self::getWBCSConfig();
		if ( $config->enabled() ) {
			global $wgCirrusSearchExtraIndexSettings;
			// Bump max fields so that labels/descriptions fields fit in.
			$wgCirrusSearchExtraIndexSettings['index.mapping.total_fields.limit'] = 5000;
		}
	}

	/**
	 * Adds the definitions relevant for Search to entity types definitions.
	 *
	 * @see WikibaseSearch.entitytypes.php
	 *
	 * @param array[] &$entityTypeDefinitions
	 */
	public static function onWikibaseRepoEntityTypes( array &$entityTypeDefinitions ) {
		$wbcsConfig = self::getWBCSConfig();
		if ( !$wbcsConfig->enabled() ) {
			return;
		}
		$entityTypeDefinitions = wfArrayPlus2d(
			require __DIR__ . '/../WikibaseSearch.entitytypes.php',
			$entityTypeDefinitions
		);
	}

	/**
	 * Add Wikibase-specific ElasticSearch analyzer configurations.
	 * @param array &$config
	 * @param AnalysisConfigBuilder $builder
	 */
	public static function onCirrusSearchAnalysisConfig( &$config, AnalysisConfigBuilder $builder ) {
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			return;
		}
		$wbcsConfig = self::getWBCSConfig();
		if ( !$wbcsConfig->enabled() ) {
			return;
		}
		static $inHook;
		if ( $inHook ) {
			// Do not call this hook repeatedly, since ConfigBuilder calls AnalysisConfigBuilder
			// FIXME: this is not a very nice hack, but we need it because we want AnalysisConfigBuilder
			// to call the hook, since other extensions may make relevant changes to config.
			// We just don't want to run this specific hook again, but Mediawiki API does not have
			// the means to exclude one hook temporarily.
			return;
		}

		// Analyzer for splitting statements and extracting properties:
		// P31=Q1234 => P31
		$config['analyzer']['extract_wb_property'] = [
			'type' => 'custom',
			'tokenizer' => 'split_wb_statements',
			'filter' => [ 'first_token' ],
		];
		$config['tokenizer']['split_wb_statements'] = [
			'type' => 'pattern',
			'pattern' => StatementsField::STATEMENT_SEPARATOR,
		];
		$config['filter']['first_token'] = [
			'type' => 'limit',
			'max_token_count' => 1
		];

		// Analyzer for extracting quantity data and storing it in a term frequency field
		$config['analyzer']['extract_wb_quantity'] = [
			'type' => 'custom',
			'tokenizer' => 'keyword',
			'filter' => [ 'term_freq' ],
		];

		// Language analyzers for descriptions
		$wbBuilder = new ConfigBuilder( WikibaseRepo::getTermsLanguages()->getLanguages(),
			self::getWBCSConfig(),
			$builder
		);
		$inHook = true;
		try {
			$wbBuilder->buildConfig( $config );
		} finally {
			$inHook = false;
		}
	}

	/**
	 * Register our cirrus profiles using WikibaseRepo.
	 *
	 * @param SearchProfileService $service
	 */
	public static function onCirrusSearchProfileService( SearchProfileService $service ) {
		$config = self::getWBCSConfig();
		if ( !defined( 'MW_PHPUNIT_TEST' ) && !$config->enabled() ) {
			return;
		}

		$namespacesForContexts = [];
		$entityNsLookup = WikibaseRepo::getEntityNamespaceLookup();
		foreach ( WikibaseRepo::getFulltextSearchTypes() as $type => $profileContext ) {
			$namespace = $entityNsLookup->getEntityNamespace( $type );
			if ( $namespace === null ) {
				continue;
			}
			$namespacesForContexts[$profileContext][] = $namespace;
		}

		self::registerSearchProfiles( $service, $config, $namespacesForContexts );
	}

	/**
	 * Register config variable containing search profiles.
	 * @param string $profileName Name of the variable (in config context) that contains profiles
	 * @param string $repoType Cirrus repo type
	 * @param SearchProfileService $service
	 * @param WikibaseSearchConfig $entitySearchConfig Config object
	 */
	private static function registerArrayProfile(
		$profileName,
		$repoType,
		SearchProfileService $service,
		WikibaseSearchConfig $entitySearchConfig
	) {
		$profile = $entitySearchConfig->get( $profileName );
		if ( $profile ) {
			$service->registerArrayRepository( $repoType, 'wikibase_config', $profile );
		}
	}

	/**
	 * Register cirrus profiles.
	 * (Visible for testing purposes)
	 * @param SearchProfileService $service
	 * @param WikibaseSearchConfig $entitySearchConfig
	 * @param int[][] $namespacesForContexts list of namespaces indexed by profile context name
	 * @see SearchProfileService
	 * @see WikibaseRepo::getFulltextSearchTypes()
	 * @throws \ConfigException
	 */
	public static function registerSearchProfiles(
		SearchProfileService $service,
		WikibaseSearchConfig $entitySearchConfig,
		array $namespacesForContexts
	) {
		$stmtBoost = $entitySearchConfig->get( 'StatementBoost' );
		// register base profiles available on all wikibase installs
		$service->registerFileRepository( SearchProfileService::RESCORE,
			'wikibase_base', __DIR__ . '/config/ElasticSearchRescoreProfiles.php' );
		$service->registerRepository( new SearchProfileRepositoryTransformer(
			ArrayProfileRepository::fromFile(
				SearchProfileService::RESCORE_FUNCTION_CHAINS,
				'wikibase_base',
				__DIR__ . '/config/ElasticSearchRescoreFunctions.php' ),
			[ EntitySearchElastic::STMT_BOOST_PROFILE_REPL => $stmtBoost ]
		) );
		$service->registerFileRepository( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			'wikibase_base', __DIR__ . '/config/EntityPrefixSearchProfiles.php' );
		$service->registerFileRepository( SearchProfileService::FT_QUERY_BUILDER,
			'wikibase_base', __DIR__ . '/config/EntitySearchProfiles.php' );

		// register custom profiles provided in the wikibase config
		self::registerArrayProfile( 'RescoreProfiles', SearchProfileService::RESCORE,
			$service, $entitySearchConfig );
		// Register function chains
		$chains = $entitySearchConfig->get( 'RescoreFunctionChains' );
		if ( $chains ) {
			$service->registerRepository( new SearchProfileRepositoryTransformer(
				ArrayProfileRepository::fromArray(
					SearchProfileService::RESCORE_FUNCTION_CHAINS,
					'wikibase_config',
					$chains ),
				[ EntitySearchElastic::STMT_BOOST_PROFILE_REPL => $stmtBoost ]
			) );
		}

		self::registerArrayProfile( 'PrefixSearchProfiles',
			EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			$service, $entitySearchConfig );
		self::registerArrayProfile( 'FulltextSearchProfiles',
			SearchProfileService::FT_QUERY_BUILDER,
			$service, $entitySearchConfig );

		// Determine the default rescore profile to use for entity autocomplete search
		$defaultRescore = $entitySearchConfig->get( 'DefaultPrefixRescoreProfile',
			EntitySearchElastic::DEFAULT_RESCORE_PROFILE );
		$service->registerDefaultProfile( SearchProfileService::RESCORE,
			EntitySearchElastic::CONTEXT_WIKIBASE_PREFIX, $defaultRescore );
		// add the possibility to override the profile by setting the URI param cirrusRescoreProfile
		$service->registerUriParamOverride( SearchProfileService::RESCORE,
			EntitySearchElastic::CONTEXT_WIKIBASE_PREFIX, 'cirrusRescoreProfile' );

		// Determine the default query builder profile to use for entity autocomplete search
		$defaultQB = $entitySearchConfig->get( 'PrefixSearchProfile',
			EntitySearchElastic::DEFAULT_QUERY_BUILDER_PROFILE );

		$service->registerDefaultProfile( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			EntitySearchElastic::CONTEXT_WIKIBASE_PREFIX, $defaultQB );
		$service->registerUriParamOverride( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			EntitySearchElastic::CONTEXT_WIKIBASE_PREFIX, 'cirrusWBProfile' );

		// Determine query builder profile for fulltext search
		$defaultFQB = $entitySearchConfig->get( 'FulltextSearchProfile',
			EntitySearchElastic::DEFAULT_FULL_TEXT_QUERY_BUILDER_PROFILE );

		$service->registerDefaultProfile( SearchProfileService::FT_QUERY_BUILDER,
			EntitySearchElastic::CONTEXT_WIKIBASE_FULLTEXT, $defaultFQB );
		$service->registerUriParamOverride( SearchProfileService::FT_QUERY_BUILDER,
			EntitySearchElastic::CONTEXT_WIKIBASE_FULLTEXT, 'cirrusWBProfile' );

		// Determine the default rescore profile to use for fulltext search
		$defaultFTRescore = $entitySearchConfig->get( 'DefaultFulltextRescoreProfile',
			EntitySearchElastic::DEFAULT_RESCORE_PROFILE );

		$service->registerDefaultProfile( SearchProfileService::RESCORE,
			EntitySearchElastic::CONTEXT_WIKIBASE_FULLTEXT, $defaultFTRescore );
		// add the possibility to override the profile by setting the URI param cirrusRescoreProfile
		$service->registerUriParamOverride( SearchProfileService::RESCORE,
			EntitySearchElastic::CONTEXT_WIKIBASE_FULLTEXT, 'cirrusRescoreProfile' );

		// Declare "search routes" for wikibase full text search types
		// Source of the routes is $namespacesForContexts which is a "reversed view"
		// of WikibaseRepo::getFulltextSearchTypes().
		// It maps the namespaces to a profile context (e.g. EntitySearchElastic::CONTEXT_WIKIBASE_FULLTEXT)
		// and will tell cirrus to use the various components we declare in the SearchProfileService
		// above.
		// In this case since wikibase owns these namespaces we score the routes at 1.0 which discards
		// any other routes and eventually fails if another extension
		// tries to own our namespace.
		// For now we only accept simple bag of words queries but this will change in the future
		// when query builders will manipulate the parsed query.
		foreach ( $namespacesForContexts as $profileContext => $namespaces ) {
			Assert::precondition( is_string( $profileContext ),
				'$namespacesForContexts keys must be strings and refer to the profile context to use' );
			$service->registerFTSearchQueryRoute(
				$profileContext,
				1.0,
				$namespaces,
				// The wikibase builders only supports simple queries for now
				[ BasicQueryClassifier::SIMPLE_BAG_OF_WORDS ]
			);
		}
	}

	/**
	 * Add extra cirrus search query features for wikibase
	 *
	 * @param \CirrusSearch\SearchConfig $config (not used, required by hook)
	 * @param array &$extraFeatures
	 */
	public static function onCirrusSearchAddQueryFeatures( $config, array &$extraFeatures ) {
		$searchConfig = self::getWBCSConfig();
		if ( !$searchConfig->enabled() ) {
			return;
		}
		$extraFeatures[] = new HasWbStatementFeature();
		$extraFeatures[] = new WbStatementQuantityFeature();

		$licenseMapping = HasLicenseFeature::getConfiguredLicenseMap( $searchConfig );
		$extraFeatures[] = new HasLicenseFeature( $licenseMapping );

		$languageCodes = WikibaseRepo::getTermsLanguages()->getLanguages();
		$extraFeatures[] = new InLabelFeature( WikibaseRepo::getLanguageFallbackChainFactory(), $languageCodes );

		$extraFeatures[] = new HasDataForLangFeature( $languageCodes );
	}

	/**
	 * Will instantiate descriptions for search results.
	 * @param Language $lang
	 * @param array &$results
	 */
	public static function amendSearchResults( Language $lang, array &$results ) {
		$lookupFactory = WikibaseRepo::getLanguageFallbackLabelDescriptionLookupFactory();
		$idParser = WikibaseRepo::getEntityIdParser();
		$entityIds = [];
		$namespaceLookup = WikibaseRepo::getEntityNamespaceLookup();

		foreach ( $results as &$result ) {
			if ( empty( $result['title'] ) ||
				!$namespaceLookup->isEntityNamespace( $result['title']->getNamespace() ) ) {
				continue;
			}
			try {
				$title = $result['title']->getText();
				$entityId = $idParser->parse( $title );
				$entityIds[] = $entityId;
				$result['entityId'] = $entityId;
			} catch ( EntityIdParsingException $e ) {
				continue;
			}
		}
		if ( empty( $entityIds ) ) {
			return;
		}
		$lookup = $lookupFactory->newLabelDescriptionLookup( $lang, $entityIds );
		$formatterFactory = WikibaseRepo::getEntityLinkFormatterFactory();
		foreach ( $results as &$result ) {
			if ( empty( $result['entityId'] ) ) {
				continue;
			}
			$entityId = $result['entityId'];
			unset( $result['entityId'] );
			$label = $lookup->getLabel( $entityId );
			if ( !$label ) {
				continue;
			}
			$linkFormatter = $formatterFactory->getLinkFormatter( $entityId->getEntityType(), $lang );
			$result['extract'] = strip_tags( $linkFormatter->getHtml( $entityId, [
				'value' => $label->getText(),
				'language' => $label->getActualLanguageCode(),
			] ) );
		}
	}

	/**
	 * Will instantiate descriptions for search results.
	 * @param array &$results
	 */
	public static function onApiOpenSearchSuggest( &$results ) {
		if ( empty( $results ) ) {
			return;
		}

		self::amendSearchResults( WikibaseRepo::getUserLanguage(), $results );
	}

	/**
	 * Register special pages.
	 *
	 * @param array &$list
	 */
	public static function onSpecialPageInitList( &$list ) {
		$list['EntitiesWithoutLabel'] = [
			SpecialEntitiesWithoutPageFactory::class,
			'newSpecialEntitiesWithoutLabel'
		];

		$list['EntitiesWithoutDescription'] = [
			SpecialEntitiesWithoutPageFactory::class,
			'newSpecialEntitiesWithoutDescription'
		];
	}

	/**
	 * @return WikibaseSearchConfig
	 * @suppress PhanTypeMismatchReturnSuperType
	 */
	private static function getWBCSConfig(): WikibaseSearchConfig {
		return MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'WikibaseCirrusSearch' );
	}

}
