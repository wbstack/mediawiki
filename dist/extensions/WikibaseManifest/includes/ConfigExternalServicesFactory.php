<?php

namespace MediaWiki\Extension\WikibaseManifest;

use Config;

class ConfigExternalServicesFactory implements ExternalServicesFactory {
	private const WB_REPO_SETTINGS = 'WBRepoSettings';
	private const WB_REPO_SPARQL_ENDPOINT = 'sparqlEndpoint';

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var string
	 */
	private $configMappingName;

	public function __construct( Config $config, string $configMappingName ) {
		$this->config = $config;
		$this->configMappingName = $configMappingName;
	}

	public function getExternalServices(): ExternalServices {
		return new ExternalServices(
			array_merge(
				$this->getArrayForWikibaseConfiguredSparql(),
				$this->config->get( $this->configMappingName )
			)
		);
	}

	private function getArrayForWikibaseConfiguredSparql() {
		$sparql = $this->getWikibaseConfiguredSparqlOrNull();
		return $sparql === null ? [] : [ ExternalServices::KEY_QUERYSERVICE => $sparql ];
	}

	private function getWikibaseConfiguredSparqlOrNull() {
		// TODO this might not exist as we can't define a dependency on Wikbase as it doesnt use
		// extension.json yet
		return $this->config->get( self::WB_REPO_SETTINGS )[self::WB_REPO_SPARQL_ENDPOINT] ?? null;
	}
}
