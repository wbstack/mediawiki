<?php

use MediaWiki\Extension\WikibaseManifest\ConceptNamespaces;
use MediaWiki\Extension\WikibaseManifest\ConfigEquivEntitiesFactory;
use MediaWiki\Extension\WikibaseManifest\ConfigExternalServicesFactory;
use MediaWiki\Extension\WikibaseManifest\ConfigMaxLagFactory;
use MediaWiki\Extension\WikibaseManifest\EmptyValueCleaner;
use MediaWiki\Extension\WikibaseManifest\LocalSourceEntityNamespacesFactory;
use MediaWiki\Extension\WikibaseManifest\ManifestGenerator;
use MediaWiki\Extension\WikibaseManifest\OAuthUrlFactory;
use MediaWiki\Extension\WikibaseManifest\TitleFactoryMainPageUrl;
use MediaWiki\Extension\WikibaseManifest\WbManifest;
use MediaWiki\MediaWikiServices;
use Wikibase\Repo\WikibaseRepo;

return [
	WbManifest::WIKIBASE_MANIFEST_GENERATOR => static function ( MediaWikiServices $services ) {
		$mainPageUrl =
			$services->getService( WbManifest::WIKIBASE_MANIFEST_TITLE_FACTORY_MAIN_PAGE_URL );

		$equivEntitiesFactory =
			$services->getService( WbManifest::WIKIBASE_MANIFEST_CONFIG_EQUIV_ENTITIES_FACTORY );

		$conceptNamespaces = $services->getService( WbManifest::WIKIBASE_MANIFEST_CONCEPT_NAMESPACES );

		$externalServicesFactory = $services->getService( WbManifest::WIKIBASE_MANIFEST_CONFIG_EXTERNAL_SERVICES_FACTORY );

		$entityNamespacesFactory = $services->getService( WbManifest::WIKIBASE_MANIFEST_LOCAL_SOURCE_ENTITY_NAMESPACES_FACTORY );

		$maxLagFactory = $services->getService( WbManifest::WIKIBASE_MANIFEST_CONFIG_MAX_LAG_FACTORY );

		$oauthUrlFactory = $services->getService( WbManifest::OAUTH_URL_FACTORY );

		return new ManifestGenerator(
			$services->getMainConfig(),
			$mainPageUrl,
			$equivEntitiesFactory,
			$conceptNamespaces,
			$externalServicesFactory,
			$entityNamespacesFactory,
			$maxLagFactory,
			$oauthUrlFactory
		);
	},
	WbManifest::WIKIBASE_MANIFEST_CONFIG_EQUIV_ENTITIES_FACTORY => static function ( MediaWikiServices $services ) {
		return new ConfigEquivEntitiesFactory(
			$services->getMainConfig(), WbManifest::ENTITY_MAPPING_CONFIG
		);
	},
	WbManifest::WIKIBASE_MANIFEST_CONFIG_EXTERNAL_SERVICES_FACTORY => static function ( MediaWikiServices $services ) {
		return new ConfigExternalServicesFactory(
			$services->getMainConfig(), WbManifest::EXTERNAL_SERVICES_CONFIG
		);
	},
	WbManifest::WIKIBASE_MANIFEST_CONCEPT_NAMESPACES => static function ( MediaWikiServices $services ) {
		$rdfVocabulary = WikibaseRepo::getRdfVocabulary( $services );
		$localEntitySource = WikibaseRepo::getLocalEntitySource( $services );
		// TODO: Get Canonical Document URLS from a service not straight from remote
		return new ConceptNamespaces( $localEntitySource, $rdfVocabulary );
	},
	WbManifest::EMPTY_VALUE_CLEANER => static function () {
		return new EmptyValueCleaner();
	},
	WbManifest::WIKIBASE_MANIFEST_LOCAL_SOURCE_ENTITY_NAMESPACES_FACTORY => static function ( MediaWikiServices $services
	) {
		$localEntitySource = WikibaseRepo::getLocalEntitySource( $services );

		return new LocalSourceEntityNamespacesFactory(
			$localEntitySource, $services->getNamespaceInfo()
		);
	},
	WbManifest::WIKIBASE_MANIFEST_TITLE_FACTORY_MAIN_PAGE_URL => static function ( MediaWikiServices $services ) {
		return new TitleFactoryMainPageUrl( $services->getTitleFactory() );
	},
	WbManifest::WIKIBASE_MANIFEST_CONFIG_MAX_LAG_FACTORY => static function ( MediaWikiServices $services ) {
		return new ConfigMaxLagFactory(
			$services->getMainConfig(), WbManifest::MAX_LAG_CONFIG
		);
	},
	WbManifest::OAUTH_URL_FACTORY => static function ( MediaWikiServices $services ) {
		return new OAuthUrlFactory(
			$services->getMainConfig(),
			ExtensionRegistry::getInstance(),
			$services->getSpecialPageFactory()
		);
	}
];
