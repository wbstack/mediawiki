'use strict';

/**
 * @class
 * @property {Object.<string,string>} cache
 *
 * @constructor
 */
const TitleCache = function () {
	this.cache = {};
};

/**
 * @param {string} name
 * @return {string}
 */
const getCacheKey = function ( name ) {
	// Note: The Title class is used for normalization, even if the incoming strings aren't page
	// titles, but namespace names.
	try {
		return ( new mw.Title( name ) ).getPrefixedDb();
	} catch ( e ) {
		return name;
	}
};

/**
 * @param {string} name
 * @return {string}
 */
TitleCache.prototype.get = function ( name ) {
	return this.cache[ getCacheKey( name ) ];
};

/**
 * @param {string} name
 * @param {string} value
 */
TitleCache.prototype.set = function ( name, value ) {
	this.cache[ getCacheKey( name ) ] = value;
};

/**
 * @param {string} name
 * @return {boolean}
 */
TitleCache.prototype.has = function ( name ) {
	return Object.prototype.hasOwnProperty.call( this.cache, getCacheKey( name ) );
};

module.exports = TitleCache;
