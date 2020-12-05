( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	var getOptions = function ( optionProvider ) {
		return [ { data: '', label: mw.msg( 'advancedsearch-filetype-default' ) } ]
			.concat( optionProvider.getFileGroupOptions() )
			.concat( optionProvider.getAllowedFileTypeOptions() );
	};

	/**
	 * @class
	 * @extends {OO.ui.DropdownInputWidget}
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {mw.libs.advancedSearch.dm.FileTypeOptionProvider} optionProvider
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.FileTypeSelection = function ( store, optionProvider, config ) {
		config = $.extend( { options: getOptions( optionProvider ) }, config );
		this.className = 'mw-advancedSearch-filetype-';
		mw.libs.advancedSearch.ui.FileTypeSelection.parent.call( this, store, config );
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.FileTypeSelection, mw.libs.advancedSearch.ui.StoreListener );
	OO.mixinClass( mw.libs.advancedSearch.ui.FileTypeSelection, mw.libs.advancedSearch.ui.ClassesForDropdownOptions );

}() );
