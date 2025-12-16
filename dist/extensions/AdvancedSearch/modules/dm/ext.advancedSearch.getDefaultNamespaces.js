'use strict';

/**
 * Get the default search namespace ids from user settings
 *
 * @param {Object.<string,string>} userSettings User settings like in mw.user.fields.values
 * @return {string[]} Namespace ids
 */
const getDefaultNamespaces = function ( userSettings ) {
	const defaultNamespaces = [];
	for ( const key in userSettings ) {
		if ( userSettings[ key ] ) {
			const matches = key.match( /^searchNs(\d+)$/ );
			if ( matches ) {
				defaultNamespaces.push( matches[ 1 ] );
			}
		}
	}
	return defaultNamespaces;
};

module.exports = getDefaultNamespaces;
