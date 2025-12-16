'use strict';

const getDefaultNamespaces = require( './ext.advancedSearch.getDefaultNamespaces.js' );
const { arrayContains } = require( '../ext.advancedSearch.util.js' );

/**
 * @class
 * @property {Object.<int,string>} namespaces
 * @property {Object.<string,Function>} providerFunctions
 *
 * @constructor
 * @param {Object.<int,string>} namespaces Mapping namespace ids to localized names
 */
const NamespacePresetProviders = function ( namespaces ) {
	this.namespaces = namespaces;
	this.providerFunctions = {
		all: ( namespaceIds ) => namespaceIds,
		discussion: ( namespaceIds ) => namespaceIds.filter( mw.Title.isTalkNamespace ),
		defaultNamespaces: () => getDefaultNamespaces( mw.user.options.values )
	};

	/**
	 * Fired after the default namespace preset provider functions have been registered. Hook
	 * handlers can add additional presets and modify or remove existing ones. See docs/settings.md
	 * for an example.
	 *
	 * @event advancedSearch.initNamespacePresetProviders
	 * @param {Object.<string,Function>} providerFunctions
	 * @stable to use
	 */
	mw.hook( 'advancedSearch.initNamespacePresetProviders' ).fire( this.providerFunctions );
};

OO.initClass( NamespacePresetProviders );

/**
 * @param {string} providerName
 * @return {boolean}
 */
NamespacePresetProviders.prototype.hasProvider = function ( providerName ) {
	return Object.prototype.hasOwnProperty.call( this.providerFunctions, providerName );
};

/**
 * @param {string} providerName
 * @return {string[]}
 */
NamespacePresetProviders.prototype.getNamespaceIdsFromProvider = function ( providerName ) {
	const self = this;

	return this.providerFunctions[ providerName ]( Object.keys( this.namespaces ) )
		// Calling String() as a function casts numbers to strings
		.map( String )
		.filter( ( id ) => {
			if ( id in self.namespaces ) {
				return true;
			}
			mw.log.warn( 'AdvancedSearch namespace preset provider "' + providerName + '" returned invalid namespace id' );
			return false;
		} );
};

/**
 * @param {string[]} namespaceIds
 * @return {boolean}
 */
NamespacePresetProviders.prototype.namespaceIdsAreValid = function ( namespaceIds ) {
	return arrayContains( Object.keys( this.namespaces ), namespaceIds );
};

module.exports = NamespacePresetProviders;
