( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @class
	 * @extends {OO.ui.TextInputWidget}
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.TextInput = function ( store, config ) {
		config = $.extend( {}, config );
		this.store = store;
		this.fieldId = config.fieldId;

		this.store.connect( this, { update: 'onStoreUpdate' } );

		mw.libs.advancedSearch.ui.TextInput.parent.call( this, config );

		this.populateFromStore();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.TextInput, OO.ui.TextInputWidget );

	mw.libs.advancedSearch.ui.TextInput.prototype.onStoreUpdate = function () {
		this.populateFromStore();
	};

	mw.libs.advancedSearch.ui.TextInput.prototype.populateFromStore = function () {
		if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
			this.setValue( this.store.getField( this.fieldId ) );
		}
	};

}() );
