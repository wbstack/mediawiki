<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Request\WebRequest;
use Wikibase\DataModel\Services\Lookup\InProcessCachingDataTypeLookup;
use Wikibase\Lexeme\DataAccess\Store\NullLabelDescriptionLookup;
use Wikibase\Lexeme\Search\Elastic\FormSearchEntity;
use Wikibase\Lexeme\Search\Elastic\LexemeFieldDefinitions;
use Wikibase\Lexeme\Search\Elastic\LexemeFullTextQueryBuilder;
use Wikibase\Lexeme\Search\Elastic\LexemeSearchEntity;
use Wikibase\Lib\EntityTypeDefinitions as Def;
use Wikibase\Lib\SettingsArray;
use Wikibase\Repo\Api\CombinedEntitySearchHelper;
use Wikibase\Repo\Api\EntityIdSearchHelper;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Search\Elastic\Fields\StatementProviderFieldDefinitions;

return [
	'lexeme' => [
		Def::SEARCH_FIELD_DEFINITIONS => static function ( array $languageCodes, SettingsArray $searchSettings ) {
			$services = MediaWikiServices::getInstance();
			$config = $services->getMainConfig();
			if ( $config->has( 'LexemeLanguageCodePropertyId' ) ) {
				$lcID = $config->get( 'LexemeLanguageCodePropertyId' );
			} else {
				$lcID = null;
			}
			return new LexemeFieldDefinitions(
				StatementProviderFieldDefinitions::newFromSettings(
					new InProcessCachingDataTypeLookup(
						WikibaseRepo::getPropertyDataTypeLookup( $services ) ),
					WikibaseRepo::getDataTypeDefinitions( $services )
						->getSearchIndexDataFormatterCallbacks(),
					$searchSettings
				),
				WikibaseRepo::getEntityLookup( $services ),
				$lcID
					? WikibaseRepo::getEntityIdParser( $services )->parse( $lcID )
					: null
			);
		},
		Def::ENTITY_SEARCH_CALLBACK => static function ( WebRequest $request ) {
			$fallbackTermLookupFactory = WikibaseRepo::getFallbackLabelDescriptionLookupFactory();
			$entityIdParser = WikibaseRepo::getEntityIdParser();

			return new CombinedEntitySearchHelper(
				[
					new EntityIdSearchHelper(
						WikibaseRepo::getEntityLookup(),
						$entityIdParser,
						$fallbackTermLookupFactory->newLabelDescriptionLookup( WikibaseRepo::getUserLanguage() ),
						WikibaseRepo::getEnabledEntityTypes()
					),
					new LexemeSearchEntity(
						$entityIdParser,
						$request,
						WikibaseRepo::getUserLanguage(),
						$fallbackTermLookupFactory
					)
				]
			);
		},
		Def::FULLTEXT_SEARCH_CONTEXT => LexemeFullTextQueryBuilder::CONTEXT_LEXEME_FULLTEXT,
	],
	'form' => [
		Def::ENTITY_SEARCH_CALLBACK => static function ( WebRequest $request ) {
			$entityIdParser = WikibaseRepo::getEntityIdParser();

			return new CombinedEntitySearchHelper(
				[
					new Wikibase\Repo\Api\EntityIdSearchHelper(
						WikibaseRepo::getEntityLookup(),
						$entityIdParser,
						new NullLabelDescriptionLookup(),
						WikibaseRepo::getEnabledEntityTypes()
					),
					new FormSearchEntity(
						$entityIdParser,
						$request,
						WikibaseRepo::getUserLanguage(),
						WikibaseRepo::getFallbackLabelDescriptionLookupFactory()
					),
				]
			);
		},
	],
	// TODO: support senses?
];
