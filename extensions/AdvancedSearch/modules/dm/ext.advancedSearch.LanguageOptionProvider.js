( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	/**
	 * @class
	 * @constructor
	 * @param {Object} languages in format { language-code: language-name }
	 */
	mw.libs.advancedSearch.dm.LanguageOptionProvider = function ( languages ) {
		this.languages = Object.keys( languages ).map( function ( key ) {
			return { data: key, label: key + ' - ' + languages[ key ] };
		} );
		this.languages.sort( function ( a, b ) {
			// Sort alphabetically
			return a.data.localeCompare( b.data );
		} );
	};

	OO.initClass( mw.libs.advancedSearch.dm.LanguageOptionProvider );

	mw.libs.advancedSearch.dm.LanguageOptionProvider.prototype.getLanguages = function () {
		return this.languages;
	};

}() );
