( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};

	/**
	 * @param {mw.libs.advancedSearch.SearchField[]} searchFields
	 * @class
	 * @constructor
	 */
	mw.libs.advancedSearch.QueryCompiler = function ( searchFields ) {
		this.fields = searchFields;
	};

	/**
	 * @private
	 * @param {mw.libs.advancedSearch.dm.SearchModel} state
	 * @return {string[]}
	 */
	mw.libs.advancedSearch.QueryCompiler.prototype.formatSearchFields = function ( state ) {
		var queryElements = [];

		this.fields.forEach( function ( field ) {
			var val = state.getField( field.id ),
				formattedQueryElement = val ? field.formatter( val ) : '';

			if ( formattedQueryElement ) {
				queryElements.push( formattedQueryElement );
			}
		} );

		return queryElements;
	};

	/**
	 * @param {mw.libs.advancedSearch.dm.SearchModel} state
	 * @return {string}
	 */
	mw.libs.advancedSearch.QueryCompiler.prototype.compileSearchQuery = function ( state ) {
		return this.formatSearchFields( state ).join( ' ' );
	};

	/**
	 * @param {string} haystack
	 * @param {string} needle
	 * @return {boolean}
	 */
	var stringEndsWith = function ( haystack, needle ) {
		var position = haystack.length - needle.length;
		return position >= 0 && haystack.indexOf( needle, position ) === position;
	};

	/**
	 * @param {string} search The current search string
	 * @param {mw.libs.advancedSearch.dm.SearchModel} state
	 * @return {string}
	 */
	mw.libs.advancedSearch.QueryCompiler.prototype.removeCompiledQueryFromSearch = function ( search, state ) {
		var advancedQuery = this.compileSearchQuery( state );
		if ( advancedQuery && stringEndsWith( search, advancedQuery ) ) {
			return search.slice( 0, -advancedQuery.length ).replace( /\s+$/, '' );
		}
		return search;
	};

}() );
