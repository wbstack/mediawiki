<?php

namespace AdvancedSearch;

/**
 * @license GPL-2.0-or-later
 */
class SearchableNamespaceListBuilder {

	private const MAIN_NAMESPACE = 'blanknamespace';

	/**
	 * Get a curated list of namespaces. Adds Main namespace and removes unnamed namespaces
	 * @param string[] $configNamespaces Key is namespace ID and value namespace string
	 * @return string[]
	 */
	public static function getCuratedNamespaces( array $configNamespaces ) {
		self::addMainNamespace( $configNamespaces );
		self::filterUnnamedNamespaces( $configNamespaces );
		return $configNamespaces;
	}

	/**
	 * @param string[] &$configNamespaces
	 */
	private static function addMainNamespace( array &$configNamespaces ) {
		$configNamespaces[ NS_MAIN ] = wfMessage( self::MAIN_NAMESPACE )->text();
	}

	/**
	 * @param string[] &$configNamespaces
	 */
	private static function filterUnnamedNamespaces( array &$configNamespaces ) {
		$configNamespaces = array_filter( $configNamespaces );
	}
}
