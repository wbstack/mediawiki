( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @class
	 * @extends OO.ui.DropdownInputWidget
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 */

	mw.libs.advancedSearch.ui.StoreListener = function ( store, config ) {
		this.store = store;
		this.fieldId = config.fieldId;

		store.connect( this, { update: 'onStoreUpdate' } );
		mw.libs.advancedSearch.ui.StoreListener.parent.call( this, config );
		this.setValueFromStore();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.StoreListener, OO.ui.DropdownInputWidget );

	mw.libs.advancedSearch.ui.StoreListener.prototype.onStoreUpdate = function () {
		this.setValueFromStore();
	};

	mw.libs.advancedSearch.ui.StoreListener.prototype.setValueFromStore = function () {
		if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
			this.setValue( this.store.getField( this.fieldId ) );
		}
	};

}() );
