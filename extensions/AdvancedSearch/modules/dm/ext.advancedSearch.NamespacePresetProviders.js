( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	/**
	 * Fired when the namespace ID providers are initialized
	 *
	 * The real event name is `advancedSearch.initNamespacePresetProviders`, but jsDuck does not support dots in event names.
	 *
	 * @event advancedSearch_initNamespacePresetProviders
	 * @param {Object} providerFunctions
	 */

	/**
	 * @param {Array} namespaces
	 * @constructor
	 */
	mw.libs.advancedSearch.dm.NamespacePresetProviders = function ( namespaces ) {
		this.namespaces = namespaces;
		this.providerFunctions = {
			all: function ( namespaceIds ) {
				return namespaceIds;
			},
			discussion: function ( namespaceIds ) {
				return namespaceIds.filter( mw.Title.isTalkNamespace );
			},
			defaultNamespaces: function () {
				return mw.libs.advancedSearch.dm.getDefaultNamespaces( mw.user.options.values );
			}
		};
		mw.hook( 'advancedSearch.initNamespacePresetProviders' ).fire( this.providerFunctions );
	};

	OO.initClass( mw.libs.advancedSearch.dm.NamespacePresetProviders );

	mw.libs.advancedSearch.dm.NamespacePresetProviders.prototype.hasProvider = function ( providerName ) {
		return Object.prototype.hasOwnProperty.call( this.providerFunctions, providerName );
	};

	/**
	 * @param {string} providerName
	 * @return {string[]}
	 */
	mw.libs.advancedSearch.dm.NamespacePresetProviders.prototype.getNamespaceIdsFromProvider = function ( providerName ) {
		var self = this;

		return this.providerFunctions[ providerName ]( Object.keys( this.namespaces ) )
			// Calling String() as a function casts numbers to strings
			.map( String )
			.filter( function ( id ) {
				if ( id in self.namespaces ) {
					return true;
				}
				mw.log.warn( 'AdvancedSearch namespace preset provider "' + providerName + '" returned invalid namespace ID' );
				return false;
			} );
	};

	/**
	 * @param {string[]} namespaceIds
	 * @return {boolean}
	 */
	mw.libs.advancedSearch.dm.NamespacePresetProviders.prototype.namespaceIdsAreValid = function ( namespaceIds ) {
		return mw.libs.advancedSearch.util.arrayContains( Object.keys( this.namespaces ), namespaceIds );
	};

}() );
