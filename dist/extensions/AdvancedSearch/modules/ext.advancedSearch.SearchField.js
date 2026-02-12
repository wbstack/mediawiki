'use strict';

/**
 * Base class for search form fields
 *
 * @abstract
 * @class
 * @property {string} id
 * @property {string|string[]} defaultValue
 * @property {Function} formatter A callback returning a string
 * @property {Function} init A callback returning a {@see OO.ui.Widget}
 * @property {boolean} [customEventHandling]
 * @property {Function} [enabled] A callback returning a boolean
 * @property {Function} layout A callback returning a {@see OO.ui.FieldLayout}
 *
 * @constructor
 * @param {string} id
 * @param {string|string[]} [defaultValue='']
 */
const SearchField = function ( id, defaultValue ) {
	this.id = id;
	this.defaultValue = defaultValue || '';
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
const createSearchFieldFromObject = function ( obj ) {
	const id = obj.id,
		defaultValue = obj.defaultValue || '';
	delete obj.id;
	delete obj.defaultValue;
	const SearchFieldSubclass = function () {
		SearchField.apply( this, arguments );
	};
	SearchFieldSubclass.prototype = Object.create( SearchField.prototype );
	Object.assign( SearchFieldSubclass.prototype, obj );
	return new SearchFieldSubclass( id, defaultValue );
};

module.exports = {
	SearchField,
	createSearchFieldFromObject
};
