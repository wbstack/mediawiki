( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};

	mw.libs.advancedSearch.FieldCollection = function () {
		this.fields = [];
		this.groupLookup = {};
		this.frozenGroups = [];
	};

	/**
	 * @param {mw.libs.advancedSearch.SearchField} field
	 * @param {string} group
	 */
	mw.libs.advancedSearch.FieldCollection.prototype.add = function ( field, group ) {
		if ( field.id in this.groupLookup ) {
			throw new Error( 'A field with this ID (' + field.id + ') has already been added.' );
		}
		if ( this.frozenGroups.indexOf( group ) > -1 ) {
			throw new Error( 'Group "' + group + '" is frozen and does not accept more fields.' );
		}
		this.fields.push( field );
		this.groupLookup[ field.id ] = group;
	};

	/**
	 * @param {string} fieldId
	 * @return {string[]}
	 */
	mw.libs.advancedSearch.FieldCollection.prototype.getGroup = function ( fieldId ) {
		return this.groupLookup[ fieldId ];
	};

	/**
	 * @return {string[]}
	 */
	mw.libs.advancedSearch.FieldCollection.prototype.getFieldIds = function () {
		return Object.keys( this.groupLookup );
	};

	/**
	 * @param {string[]} groups
	 */
	mw.libs.advancedSearch.FieldCollection.prototype.freezeGroups = function ( groups ) {
		this.frozenGroups = this.frozenGroups.concat( groups );
	};

}() );
