'use strict';

const ClassesForDropdownOptions = require( './mixins/ext.advancedSearch.ClassesForDropdownOptions.js' );
const StoreListener = require( './ext.advancedSearch.StoreListener.js' );

/**
 * @param {FileTypeOptionProvider} optionProvider
 * @return {Object[]}
 */
const getOptions = function ( optionProvider ) {
	return [ { data: '', label: mw.msg( 'advancedsearch-filetype-default' ) } ]
		.concat( optionProvider.getFileGroupOptions() )
		.concat( optionProvider.getAllowedFileTypeOptions() );
};

/**
 * @class
 * @extends StoreListener
 *
 * @constructor
 * @param {SearchModel} store
 * @param {FileTypeOptionProvider} optionProvider
 * @param {Object} config
 */
const FileTypeSelection = function ( store, optionProvider, config ) {
	config = Object.assign( { options: getOptions( optionProvider ) }, config );
	this.className = 'mw-advancedSearch-filetype-';
	FileTypeSelection.super.call( this, store, config );
};

OO.inheritClass( FileTypeSelection, StoreListener );
OO.mixinClass( FileTypeSelection, ClassesForDropdownOptions );

module.exports = FileTypeSelection;
