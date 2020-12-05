( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	var format = function ( languages ) {
		var formatted = [];
		for ( var key in languages ) {
			formatted.push( { data: key, label: key + ' - ' + languages[ key ] } );
		}
		return formatted;
	};

	var sortAlphabetically = function ( arr ) {
		return arr.sort( function ( a, b ) {
			return a.data.localeCompare( b.data );
		} );
	};

	/**
	 * @class
	 * @constructor
	 * @param {Object} languages in format { language-code: language-name }
	 */
	mw.libs.advancedSearch.dm.LanguageOptionProvider = function ( languages ) {
		this.languages = sortAlphabetically( format( languages ) );
	};

	OO.initClass( mw.libs.advancedSearch.dm.LanguageOptionProvider );

	mw.libs.advancedSearch.dm.LanguageOptionProvider.prototype.getLanguages = function () {
		return this.languages;
	};

}() );
