<?php

namespace Wikibase\Search\Elastic;

use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigException;
use MediaWiki\Config\GlobalVarConfig;

/**
 * Config class for Wikibase search configs.
 * Provides BC wrapper for old Wikibase search configs.
 */
class WikibaseSearchConfig implements Config {

	private const WIKIBASE_SEARCH_CONFIG_PREFIX = 'wgWBCS';

	/**
	 * Global config.
	 * @var GlobalVarConfig
	 */
	private $globals;

	/**
	 * Wikibase entitySearch config - for BC.
	 * @var array
	 */
	private $wikibaseSettings;

	public function __construct( array $wikibaseSettings ) {
		$this->globals = new GlobalVarConfig( self::WIKIBASE_SEARCH_CONFIG_PREFIX );
		$this->wikibaseSettings = $wikibaseSettings;
	}

	/**
	 * Create config from globals
	 * @return WikibaseSearchConfig
	 */
	public static function newFromGlobals() {
		// BC with Wikidata settings
		return new static( $GLOBALS['wgWBRepoSettings']['entitySearch'] ?? [] );
	}

	/**
	 * Get a configuration variable such as "Sitename" or "UploadMaintenance."
	 * @param string $name Name of configuration option
	 * @param mixed $default Return if value not found.
	 * @return mixed Value configured
	 * @throws ConfigException
	 */
	public function get( $name, $default = null ) {
		$compat_name = lcfirst( $name );
		// TODO: deprecate and remove these BC settings
		if ( array_key_exists( $compat_name, $this->wikibaseSettings ) ) {
			return $this->wikibaseSettings[$compat_name];
		}
		if ( $this->globals->has( $name ) ) {
			$value = $this->globals->get( $name );
			if ( $value !== null ) {
				return $value;
			}
		}
		return $default;
	}

	/**
	 * Check whether a configuration option is set for the given name
	 *
	 * @param string $name Name of configuration option
	 * @return bool
	 */
	public function has( $name ) {
		return $this->globals->has( $name ) ||
				array_key_exists( lcfirst( $name ), $this->wikibaseSettings );
	}

	/**
	 * Check whether search functionality for this extension is enabled.
	 *
	 * @return bool
	 */
	public function enabled() {
		// Ignore Wikibase setting, it should not disable this one
		return $this->globals->get( 'UseCirrus' );
	}

}
