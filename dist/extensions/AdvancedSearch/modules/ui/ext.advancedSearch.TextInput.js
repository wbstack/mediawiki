'use strict';

/**
 * @class
 * @extends OO.ui.TextInputWidget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} config
 * @param {string} config.fieldId Field name
 */
const TextInput = function ( store, config ) {
	this.store = store;
	this.fieldId = config.fieldId;

	this.store.connect( this, { update: 'onStoreUpdate' } );

	TextInput.super.call( this, config );

	this.populateFromStore();
};

OO.inheritClass( TextInput, OO.ui.TextInputWidget );

TextInput.prototype.onStoreUpdate = function () {
	this.populateFromStore();
};

TextInput.prototype.populateFromStore = function () {
	if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
		this.setValue( this.store.getField( this.fieldId ) );
	}
};

module.exports = TextInput;
