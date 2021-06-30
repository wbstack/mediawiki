<?php

use MediaWiki\MediaWikiServices;
use Wikibase\DataModel\Services\Lookup\InProcessCachingDataTypeLookup;
use Wikibase\Lexeme\Search\Elastic\FormSearchEntity;
use Wikibase\Lexeme\Search\Elastic\LexemeFieldDefinitions;
use Wikibase\Lexeme\Search\Elastic\LexemeFullTextQueryBuilder;
use Wikibase\Lexeme\Search\Elastic\LexemeSearchEntity;
use Wikibase\Lib\SettingsArray;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Search\Elastic\Fields\StatementProviderFieldDefinitions;

return [
	'lexeme' => [
		'search-field-definitions' => function ( array $languageCodes, SettingsArray $searchSettings ) {
			$repo = WikibaseRepo::getDefaultInstance();
			$config = MediaWikiServices::getInstance()->getMainConfig();
			if ( $config->has( 'LexemeLanguageCodePropertyId' ) ) {
				$lcID = $config->get( 'LexemeLanguageCodePropertyId' );
			} else {
				$lcID = null;
			}
			return new LexemeFieldDefinitions(
				StatementProviderFieldDefinitions::newFromSettings(
					new InProcessCachingDataTypeLookup( $repo->getPropertyDataTypeLookup() ),
					$repo->getDataTypeDefinitions()->getSearchIndexDataFormatterCallbacks(),
					$searchSettings
				),
				$repo->getEntityLookup(),
				$lcID ? $repo->getEntityIdParser()->parse( $lcID ) : null
			);
		},
		'entity-search-callback' => function ( WebRequest $request ) {
			$repo = WikibaseRepo::getDefaultInstance();

			return new LexemeSearchEntity(
					$repo->getEntityIdParser(),
					$request,
					$repo->getUserLanguage(),
					$repo->getLanguageFallbackChainFactory(),
					$repo->getPrefetchingTermLookup()
			);
		},
		'fulltext-search-context' => LexemeFullTextQueryBuilder::CONTEXT_LEXEME_FULLTEXT,
	],
	'form' => [
		'entity-search-callback' => function ( WebRequest $request ) {
			$repo = WikibaseRepo::getDefaultInstance();

			return new FormSearchEntity(
					$repo->getEntityIdParser(),
					$request,
					$repo->getUserLanguage(),
					$repo->getLanguageFallbackChainFactory(),
					$repo->getPrefetchingTermLookup()
			);
		},
	],
	// TODO: support senses?
];
