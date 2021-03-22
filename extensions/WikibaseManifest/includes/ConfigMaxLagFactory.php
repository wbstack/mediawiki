<?php

namespace MediaWiki\Extension\WikibaseManifest;

use Config;

class ConfigMaxLagFactory implements MaxLagFactory {

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var string
	 */
	private $configValueName;

	public function __construct( Config $config, string $configValueName ) {
		$this->config = $config;
		$this->configValueName = $configValueName;
	}

	public function getMaxLag() : MaxLag {
		return new MaxLag( $this->config->get( $this->configValueName ) );
	}

}
