'use strict';

/**
 * @class
 * @extends OO.ui.DropdownInputWidget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} config
 * @param {string} config.fieldId Field name
 */

const StoreListener = function ( store, config ) {
	this.store = store;
	this.fieldId = config.fieldId;

	store.connect( this, { update: 'onStoreUpdate' } );
	StoreListener.super.call( this, config );
	this.setValueFromStore();
};

OO.inheritClass( StoreListener, OO.ui.DropdownInputWidget );

StoreListener.prototype.onStoreUpdate = function () {
	this.setValueFromStore();
};

StoreListener.prototype.setValueFromStore = function () {
	if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
		this.setValue( this.store.getField( this.fieldId ) );
	}
};

module.exports = StoreListener;
