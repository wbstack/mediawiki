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
	 */
	mw.libs.advancedSearch.ui.FormState = function ( store, config ) {
		this.store = store;
		mw.libs.advancedSearch.ui.FormState.parent.call( this, config );

		this.store.connect( this, { update: 'onStoreUpdate' } );

		this.populateFromStore();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.FormState, OO.ui.HiddenInputWidget );

	mw.libs.advancedSearch.ui.FormState.prototype.onStoreUpdate = function () {
		this.populateFromStore();
	};

	mw.libs.advancedSearch.ui.FormState.prototype.populateFromStore = function () {
		this.$element.val( this.store.toJSON() );
	};

}() );
