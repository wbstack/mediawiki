( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @class
	 * @extends {OO.ui.TagMultiselectWidget}
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.TemplateSearch = function ( store, config ) {
		this.store = store;

		mw.libs.advancedSearch.ui.TemplateSearch.parent.call( this, store, config );

		this.$element.addClass( 'mw-advancedSearch-template' );

		this.populateFromStore();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.TemplateSearch, mw.libs.advancedSearch.dm.MultiselectLookup );

	mw.libs.advancedSearch.ui.TemplateSearch.prototype.onStoreUpdate = function () {
		this.populateFromStore();
	};

	mw.libs.advancedSearch.ui.TemplateSearch.prototype.populateFromStore = function () {
		if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
			this.setValue( this.store.getField( this.fieldId ) );
		}
	};

	/**
	 * Update external states on internal updates
	 */
	mw.libs.advancedSearch.ui.TemplateSearch.prototype.onValueUpdate = function () {
		this.store.storeField( this.fieldId, this.getValue() );
	};

}() );
