<?php

namespace AdvancedSearch;

/**
 * @license GPL-2.0-or-later
 */
class SearchableNamespaceListBuilder {

	/**
	 * Get a curated list of namespaces. Adds Main namespace and removes unnamed namespaces
	 * @param array<int,string> $configNamespaces Mapping namespace ids to localized names
	 * @return array<int,string>
	 */
	public static function getCuratedNamespaces( array $configNamespaces ): array {
		// Make sure the main namespace is listed with a non-empty name
		if ( isset( $configNamespaces[NS_MAIN] ) && !$configNamespaces[NS_MAIN] ) {
			$configNamespaces[NS_MAIN] = wfMessage( 'blanknamespace' )->text();
		}

		// Remove entries that still have an empty name
		$configNamespaces = array_filter( $configNamespaces );

		ksort( $configNamespaces );
		return $configNamespaces;
	}

}
