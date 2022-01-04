( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	var getOptions = function ( selected ) {
		var options = mw.libs.advancedSearch.dm.getSortMethods().map( function ( name ) {
			// The currently active sort method already appears in the list, don't add it again
			if ( name === selected ) {
				selected = undefined;
			}
			// The following messages are used here:
			// * advancedsearch-sort-relevance
			// * advancedsearch-sort-*
			var msg = mw.message( 'advancedsearch-sort-' + name.replace( /_/g, '-' ) );
			return { data: name, label: msg.exists() ? msg.text() : name };
		} );
		if ( selected ) {
			// The following messages are used here:
			// * advancedsearch-sort-relevance
			// * advancedsearch-sort-*
			var msg = mw.message( 'advancedsearch-sort-' + selected.replace( /_/g, '-' ) );
			options.push( { data: selected, label: msg.exists() ? msg.text() : selected } );
		}
		return options;
	};

	mw.libs.advancedSearch.ui.SortPreference = function ( store, config ) {
		this.store = store;
		config = $.extend( {
			options: getOptions( store.getSortMethod() )
		}, config );
		this.className = 'mw-advancedSearch-sort-';

		store.connect( this, { update: 'onStoreUpdate' } );
		mw.libs.advancedSearch.ui.SortPreference.parent.call( this, config );
		this.setValueFromStore();
		this.connect( this, { change: 'onValueUpdate' } );
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.SortPreference, OO.ui.DropdownInputWidget );
	OO.mixinClass( mw.libs.advancedSearch.ui.SortPreference, mw.libs.advancedSearch.ui.ClassesForDropdownOptions );

	mw.libs.advancedSearch.ui.SortPreference.prototype.onStoreUpdate = function () {
		this.setValueFromStore();
	};

	mw.libs.advancedSearch.ui.SortPreference.prototype.setValueFromStore = function () {
		this.setValue( this.store.getSortMethod() );
	};

	mw.libs.advancedSearch.ui.SortPreference.prototype.onValueUpdate = function () {
		this.store.setSortMethod( this.getValue() );
	};

}() );
