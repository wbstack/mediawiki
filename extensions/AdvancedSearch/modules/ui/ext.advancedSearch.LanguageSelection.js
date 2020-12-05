( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	var getOptions = function ( optionProvider ) {
		var languages = optionProvider.getLanguages();
		return [ { data: '', label: mw.msg( 'advancedsearch-inlanguage-default' ) } ].concat( languages );
	};

	/**
	 * @class
	 * @extends {OO.ui.DropdownInputWidget}
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {mw.libs.advancedSearch.dm.LanguageOptionProvider} optionProvider
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.LanguageSelection = function ( store, optionProvider, config ) {
		config = $.extend( { options: getOptions( optionProvider ) }, config );
		this.className = 'mw-advancedSearch-inlanguage-';
		mw.libs.advancedSearch.ui.LanguageSelection.parent.call( this, store, config );
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.LanguageSelection, mw.libs.advancedSearch.ui.StoreListener );
	OO.mixinClass( mw.libs.advancedSearch.ui.LanguageSelection, mw.libs.advancedSearch.ui.ClassesForDropdownOptions );

}() );
