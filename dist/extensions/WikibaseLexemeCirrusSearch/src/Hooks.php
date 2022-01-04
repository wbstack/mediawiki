<?php

namespace Wikibase\Lexeme\Search\Elastic;

use CirrusSearch\Profile\ConfigProfileRepository;
use CirrusSearch\Profile\SearchProfileService;
use MediaWiki\MediaWikiServices;
use SearchResult;
use SpecialSearch;
use Wikibase\Lib\WikibaseSettings;
use Wikibase\Search\Elastic\EntitySearchElastic;

/**
 * MediaWiki hook handlers for the WikibaseLexemeCirrusSearch extension.
 *
 * @license GPL-2.0-or-later
 */
class Hooks {

	/**
	 * Adds the definition of the lexeme entity type to the definitions array Wikibase uses.
	 *
	 * @see WikibaseLexeme.entitytypes.php
	 * @see WikibaseLexeme.entitytypes.repo.php
	 *
	 * @param array[] $entityTypeDefinitions
	 */
	public static function onWikibaseRepoEntityTypes( array &$entityTypeDefinitions ) {
		if ( empty( $GLOBALS['wgLexemeUseCirrus'] ) ) {
			return;
		}
		$entityTypeDefinitions = wfArrayPlus2d(
			require __DIR__ . '/../WikibaseSearch.entitytypes.repo.php',
			$entityTypeDefinitions
		);
	}

	/**
	 * Register our cirrus profiles.
	 *
	 * @param SearchProfileService $service
	 */
	public static function onCirrusSearchProfileService( SearchProfileService $service ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		// Do not add Lexeme specific search stuff if we are not a repo
		if ( !WikibaseSettings::isRepoEnabled() || !$config->get( 'LexemeEnableRepo' ) ) {
			return;
		}

		// register base profiles available on all wikibase installs
		$service->registerFileRepository( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			'lexeme_base', __DIR__ . '/../config/LexemePrefixSearchProfiles.php' );
		$service->registerFileRepository( SearchProfileService::RESCORE_FUNCTION_CHAINS,
			'lexeme_base', __DIR__ . '/../config/LexemeRescoreFunctions.php' );
		$service->registerFileRepository( SearchProfileService::RESCORE,
			'lexeme_base', __DIR__ . '/../config/LexemeRescoreProfiles.php' );
		$service->registerFileRepository( SearchProfileService::FT_QUERY_BUILDER,
			'lexeme_base', __DIR__ . '/../config/LexemeSearchProfiles.php' );

		// register custom profiles provided in the WikibaseLexeme config settings
		$service->registerRepository(
			new ConfigProfileRepository( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
				'lexeme_config', 'LexemePrefixSearchProfiles', $config )
		);
		// Rescore functions for lexemes
		$service->registerRepository(
			new ConfigProfileRepository( SearchProfileService::RESCORE_FUNCTION_CHAINS,
				'lexeme_config', 'LexemeRescoreFunctions', $config )
		);

		// Determine the default rescore profile to use for entity autocomplete search
		$service->registerDefaultProfile( SearchProfileService::RESCORE,
			LexemeSearchEntity::CONTEXT_LEXEME_PREFIX,
			EntitySearchElastic::DEFAULT_RESCORE_PROFILE );
		$service->registerConfigOverride( SearchProfileService::RESCORE,
			LexemeSearchEntity::CONTEXT_LEXEME_PREFIX, $config, 'LexemePrefixRescoreProfile' );
		// add the possibility to override the profile by setting the URI param cirrusRescoreProfile
		$service->registerUriParamOverride( SearchProfileService::RESCORE,
			LexemeSearchEntity::CONTEXT_LEXEME_PREFIX, 'cirrusRescoreProfile' );

		// Determine the default query builder profile to use for entity autocomplete search
		$service->registerDefaultProfile( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			LexemeSearchEntity::CONTEXT_LEXEME_PREFIX,
			EntitySearchElastic::DEFAULT_QUERY_BUILDER_PROFILE );
		$service->registerConfigOverride( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			LexemeSearchEntity::CONTEXT_LEXEME_PREFIX, $config, 'LexemePrefixSearchProfile' );
		$service->registerUriParamOverride( EntitySearchElastic::WIKIBASE_PREFIX_QUERY_BUILDER,
			LexemeSearchEntity::CONTEXT_LEXEME_PREFIX, 'cirrusWBProfile' );

		// Determine query builder profile for fulltext search
		$service->registerDefaultProfile( SearchProfileService::FT_QUERY_BUILDER,
			LexemeFullTextQueryBuilder::CONTEXT_LEXEME_FULLTEXT,
			LexemeFullTextQueryBuilder::LEXEME_DEFAULT_PROFILE );
		$service->registerUriParamOverride( SearchProfileService::FT_QUERY_BUILDER,
			LexemeFullTextQueryBuilder::CONTEXT_LEXEME_FULLTEXT, 'cirrusWBProfile' );

		// Determine the default rescore profile to use for fulltext search
		$service->registerDefaultProfile( SearchProfileService::RESCORE,
			LexemeFullTextQueryBuilder::CONTEXT_LEXEME_FULLTEXT,
			LexemeFullTextQueryBuilder::LEXEME_DEFAULT_PROFILE );
		$service->registerConfigOverride( SearchProfileService::RESCORE,
			LexemeFullTextQueryBuilder::CONTEXT_LEXEME_FULLTEXT, $config,
			'LexemeFulltextRescoreProfile' );
		// add the possibility to override the profile by setting the URI param cirrusRescoreProfile
		$service->registerUriParamOverride( SearchProfileService::RESCORE,
			LexemeFullTextQueryBuilder::CONTEXT_LEXEME_FULLTEXT, 'cirrusRescoreProfile' );
	}

	/**
	 * @param SpecialSearch $searchPage
	 * @param SearchResult $result
	 * @param array $terms
	 * @param $link
	 * @param $redirect
	 * @param $section
	 * @param $extract
	 * @param $score
	 * @param $size
	 * @param $date
	 * @param $related
	 * @param $html
	 */
	public static function onShowSearchHit( SpecialSearch $searchPage, SearchResult $result,
		array $terms, &$link, &$redirect, &$section, &$extract, &$score, &$size, &$date, &$related,
		&$html
	) {

		if ( empty( $GLOBALS['wgLexemeUseCirrus'] ) ) {
			return;
		}
		if ( !( $result instanceof LexemeResult ) ) {
			return;
		}

		// set $size to size metrics
		$size = $searchPage->msg(
			'wikibaselexeme-search-result-stats',
			$result->getStatementCount(),
			$result->getFormCount()
		)->escaped();
	}

}
