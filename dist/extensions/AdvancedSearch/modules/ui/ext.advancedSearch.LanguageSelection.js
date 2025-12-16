'use strict';

const ClassesForDropdownOptions = require( './mixins/ext.advancedSearch.ClassesForDropdownOptions.js' );
const StoreListener = require( './ext.advancedSearch.StoreListener.js' );

/**
 * @param {LanguageOptionProvider} optionProvider
 * @return {Object[]}
 */
const getOptions = function ( optionProvider ) {
	const languages = optionProvider.getLanguages();
	return [ { data: '', label: mw.msg( 'advancedsearch-inlanguage-default' ) } ].concat( languages );
};

/**
 * @class
 * @extends StoreListener
 *
 * @constructor
 * @param {SearchModel} store
 * @param {LanguageOptionProvider} optionProvider
 * @param {Object} config
 */
const LanguageSelection = function ( store, optionProvider, config ) {
	config = Object.assign( { options: getOptions( optionProvider ) }, config );
	this.className = 'mw-advancedSearch-inlanguage-';
	LanguageSelection.super.call( this, store, config );
};

OO.inheritClass( LanguageSelection, StoreListener );
OO.mixinClass( LanguageSelection, ClassesForDropdownOptions );

module.exports = LanguageSelection;
