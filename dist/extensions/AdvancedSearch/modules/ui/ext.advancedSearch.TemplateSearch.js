'use strict';

const MultiselectLookup = require( '../dm/ext.advancedSearch.MultiselectLookup.js' );

/**
 * @class
 * @extends MultiselectLookup
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} config
 */
const TemplateSearch = function ( store, config ) {
	this.store = store;

	TemplateSearch.super.call( this, store, config );

	this.$element.addClass( 'mw-advancedSearch-template' );

	this.populateFromStore();
};

OO.inheritClass( TemplateSearch, MultiselectLookup );

TemplateSearch.prototype.onStoreUpdate = function () {
	this.populateFromStore();
};

TemplateSearch.prototype.populateFromStore = function () {
	if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
		this.setValue( this.store.getField( this.fieldId ) );
	}
};

/**
 * Update external states on internal updates
 */
TemplateSearch.prototype.onValueUpdate = function () {
	this.store.storeField( this.fieldId, this.getValue() );
};

module.exports = TemplateSearch;
