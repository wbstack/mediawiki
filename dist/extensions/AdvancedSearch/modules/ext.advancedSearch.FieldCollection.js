'use strict';

/**
 * @class
 * @property {SearchField[]} fields
 * @property {Object.<string,string>} groupLookup Field name => group name
 * @property {string[]} frozenGroups
 *
 * @constructor
 */
const FieldCollection = function () {
	this.fields = [];
	this.groupLookup = {};
	this.frozenGroups = [];
};

/**
 * @param {SearchField} field
 * @param {string} group
 */
FieldCollection.prototype.add = function ( field, group ) {
	if ( field.id in this.groupLookup ) {
		throw new Error( 'Field "' + field.id + '" has already been added.' );
	}
	if ( this.frozenGroups.indexOf( group ) !== -1 ) {
		throw new Error( 'Group "' + group + '" is frozen and does not accept more fields.' );
	}
	this.fields.push( field );
	this.groupLookup[ field.id ] = group;
};

/**
 * @param {string} fieldId
 * @return {string[]}
 */
FieldCollection.prototype.getGroup = function ( fieldId ) {
	return this.groupLookup[ fieldId ];
};

/**
 * @return {string[]}
 */
FieldCollection.prototype.getFieldIds = function () {
	return Object.keys( this.groupLookup );
};

/**
 * @param {string[]} groups
 */
FieldCollection.prototype.freezeGroups = function ( groups ) {
	this.frozenGroups = this.frozenGroups.concat( groups );
};

module.exports = FieldCollection;
