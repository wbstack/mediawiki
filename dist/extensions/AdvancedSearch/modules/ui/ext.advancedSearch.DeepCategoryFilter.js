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
const DeepCategoryFilter = function ( store, config ) {
	this.store = store;

	DeepCategoryFilter.super.call( this, store, config );

	this.$element.addClass( 'mw-advancedSearch-deepCategory' );

	this.populateFromStore();
};

OO.inheritClass( DeepCategoryFilter, MultiselectLookup );

DeepCategoryFilter.prototype.onStoreUpdate = function () {
	this.populateFromStore();
};

DeepCategoryFilter.prototype.populateFromStore = function () {
	if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
		this.setValue( this.store.getField( this.fieldId ) );
	}
};

module.exports = DeepCategoryFilter;
