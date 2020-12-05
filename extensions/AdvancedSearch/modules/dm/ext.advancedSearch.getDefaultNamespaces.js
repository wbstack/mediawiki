( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	/**
	 * Get the default search namespace IDs from user settings
	 *
	 * @param {Object} userSettings User settings like in mw.user.fields.values
	 * @return {string[]} Namespace IDs
	 */
	mw.libs.advancedSearch.dm.getDefaultNamespaces = function ( userSettings ) {
		var defaultNamespaces = [];
		Object.keys( userSettings ).forEach( function ( key ) {
			if ( userSettings[ key ] ) {
				var matches = key.match( /^searchNs(\d+)$/ );
				if ( matches ) {
					defaultNamespaces.push( matches[ 1 ] );
				}
			}
		} );
		return defaultNamespaces;
	};

}() );
