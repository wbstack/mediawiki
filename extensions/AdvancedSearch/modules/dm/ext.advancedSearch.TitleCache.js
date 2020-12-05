( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	mw.libs.advancedSearch.dm.TitleCache = function () {
		this.cache = {};
	};

	function getCacheKey( name ) {
		// Note: The Title class is used for normalization, even if the incoming strings aren't page
		// titles, but namespace names.
		return ( new mw.Title( name ) ).getPrefixedDb();
	}

	mw.libs.advancedSearch.dm.TitleCache.prototype.get = function ( name ) {
		return this.cache[ getCacheKey( name ) ];
	};

	mw.libs.advancedSearch.dm.TitleCache.prototype.set = function ( name, value ) {
		this.cache[ getCacheKey( name ) ] = value;
	};

	mw.libs.advancedSearch.dm.TitleCache.prototype.has = function ( name ) {
		return Object.prototype.hasOwnProperty.call( this.cache, getCacheKey( name ) );
	};

}() );
