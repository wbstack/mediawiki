'use strict';

/**
 * @class
 * @property {Object[]} languages
 *
 * @constructor
 * @param {Object.<string,string>} languages in format { language-code: language-name }
 */
const LanguageOptionProvider = function ( languages ) {
	this.languages = Object.keys( languages ).map( ( key ) => ( { data: key, label: key + ' - ' + languages[ key ] } ) );
	// Sort alphabetically
	this.languages.sort( ( a, b ) => a.data.localeCompare( b.data ) );
};

OO.initClass( LanguageOptionProvider );

/**
 * @return {Object[]}
 */
LanguageOptionProvider.prototype.getLanguages = function () {
	return this.languages;
};

module.exports = LanguageOptionProvider;
