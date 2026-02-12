'use strict';

/**
 * @class
 * @extends OO.ui.HiddenInputWidget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} config
 * @param {string} config.name
 */
const FormState = function ( store, config ) {
	this.store = store;
	this.name = config.name;
	FormState.super.call( this, config );

	this.store.connect( this, { update: 'onStoreUpdate' } );

	this.populateFromStore();
};

OO.inheritClass( FormState, OO.ui.HiddenInputWidget );

FormState.prototype.onStoreUpdate = function () {
	this.populateFromStore();
};

FormState.prototype.populateFromStore = function () {
	const json = this.store.toJSON();
	// To avoid noise (empty query parameters) in the URL, temporarily remove the name
	this.$element.attr( 'name', json ? this.name : null ).val( json );
};

module.exports = FormState;
