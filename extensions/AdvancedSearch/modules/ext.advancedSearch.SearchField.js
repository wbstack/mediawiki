( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};

	/**
	 * Base class for search form fields
	 *
	 * @class SearchField
	 * @param {string} id
	 * @param {string|Array} defaultValue
	 * @constructor
	 * @abstract
	 */
	mw.libs.advancedSearch.SearchField = function ( id, defaultValue ) {
		this.id = id;
		this.defaultValue = defaultValue || '';
	};

	mw.libs.advancedSearch.SearchField.prototype.createWidget = function ( state, config ) { // eslint-disable-line no-unused-vars
		throw new Error( 'You must implement the createWidget function' );
	};

	mw.libs.advancedSearch.SearchField.prototype.createLayout = function ( widget, config, state ) { // eslint-disable-line no-unused-vars
		throw new Error( 'You must implement the createLayout function' );
	};

	mw.libs.advancedSearch.SearchField.prototype.formatSearchValue = function ( value ) { // eslint-disable-line no-unused-vars
		throw new Error( 'You must implement the formatSearchValue function' );
	};

	/**
	 * @param {Object} obj
	 * @return {SearchField}
	 */
	mw.libs.advancedSearch.createSearchFieldFromObject = function ( obj ) {
		var id = obj.id, defaultValue = obj.defaultValue;
		delete obj.id;
		delete obj.defaultValue;
		var SearchFieldSubclass = function () {
			mw.libs.advancedSearch.SearchField.apply( this, arguments );
		};
		SearchFieldSubclass.prototype = Object.create( mw.libs.advancedSearch.SearchField.prototype );
		$.extend( SearchFieldSubclass.prototype, obj );
		return new SearchFieldSubclass( id, defaultValue );
	};

}() );
