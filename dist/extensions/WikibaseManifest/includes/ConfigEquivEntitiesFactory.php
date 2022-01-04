<?php

namespace MediaWiki\Extension\WikibaseManifest;

use Config;

class ConfigEquivEntitiesFactory implements EquivEntitiesFactory {

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

	public function getEquivEntities(): EquivEntities {
		return new EquivEntities( $this->config->get( $this->configMappingName ) );
	}

}
