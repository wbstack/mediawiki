( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @class
	 * @extends OO.ui.HiddenInputWidget
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 * @cfg {string} name
	 */
	mw.libs.advancedSearch.ui.FormState = function ( store, config ) {
		this.store = store;
		this.name = config.name;
		mw.libs.advancedSearch.ui.FormState.parent.call( this, config );

		this.store.connect( this, { update: 'onStoreUpdate' } );

		this.populateFromStore();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.FormState, OO.ui.HiddenInputWidget );

	mw.libs.advancedSearch.ui.FormState.prototype.onStoreUpdate = function () {
		this.populateFromStore();
	};

	mw.libs.advancedSearch.ui.FormState.prototype.populateFromStore = function () {
		var json = this.store.toJSON();
		// To avoid noise (empty query parameters) in the URL, temporarily remove the name
		this.$element.attr( 'name', json ? this.name : null ).val( json );
	};

}() );
