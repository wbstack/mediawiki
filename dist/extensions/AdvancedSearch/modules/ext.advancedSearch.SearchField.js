( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};

	/**
	 * Base class for search form fields
	 *
	 * @class SearchField
	 * @param {string} id
	 * @param {string|string[]} [defaultValue='']
	 * @constructor
	 * @abstract
	 *
	 * @property {string} id
	 * @property {string|string[]} defaultValue
	 * @property {Function} formatter A callback returning a string
	 * @property {Function} init A callback returning a {@see OO.ui.Widget}
	 * @property {boolean} [customEventHandling]
	 * @property {Function} [enabled] A callback returning a boolean
	 * @property {Function} layout A callback returning a {@see OO.ui.FieldLayout}
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
	 * @param {string} obj.id
	 * @param {string|string[]} [obj.defaultValue='']
	 * @param {Function} obj.formatter A callback returning a string
	 * @param {Function} obj.init A callback returning a {@see OO.ui.Widget}
	 * @param {boolean} [obj.customEventHandling]
	 * @param {Function} [obj.enabled] A callback returning a boolean
	 * @param {Function} obj.layout A callback returning a {@see OO.ui.FieldLayout}
	 * @return {SearchField}
	 */
	mw.libs.advancedSearch.createSearchFieldFromObject = function ( obj ) {
		var id = obj.id,
			defaultValue = obj.defaultValue || '';
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
