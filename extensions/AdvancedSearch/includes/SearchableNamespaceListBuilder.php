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
		// Make sure the main namespace is listed with a non-empty name
		$configNamespaces[ NS_MAIN ] = wfMessage( self::MAIN_NAMESPACE )->text();

		// Remove entries that still have an empty name
		$configNamespaces = array_filter( $configNamespaces );

		ksort( $configNamespaces );
		return $configNamespaces;
	}

}
