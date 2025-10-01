'use strict';

const ClassesForDropdownOptions = require( './mixins/ext.advancedSearch.ClassesForDropdownOptions.js' );
const getSortMethods = require( '../dm/ext.advancedSearch.getSortMethods.js' );

/**
 * @param {string} selected
 * @return {Object[]}
 */
const getOptions = function ( selected ) {
	const options = getSortMethods().map( ( name ) => {
		// The currently active sort method already appears in the list, don't add it again
		if ( name === selected ) {
			selected = undefined;
		}
		// The following messages are used here:
		// * advancedsearch-sort-relevance
		// * advancedsearch-sort-*
		const msg = mw.message( 'advancedsearch-sort-' + name.replace( /_/g, '-' ) );
		return { data: name, label: msg.exists() ? msg.text() : name };
	} );
	if ( selected ) {
		// The following messages are used here:
		// * advancedsearch-sort-relevance
		// * advancedsearch-sort-*
		const selectedMsg = mw.message( 'advancedsearch-sort-' + selected.replace( /_/g, '-' ) );
		options.push( { data: selected, label: selectedMsg.exists() ? selectedMsg.text() : selected } );
	}
	return options;
};

/**
 * @class
 * @extends OO.ui.DropdownInputWidget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} [config={}]
 */
const SortPreference = function ( store, config ) {
	this.store = store;
	config = Object.assign( {
		options: getOptions( store.getSortMethod() )
	}, config );
	this.className = 'mw-advancedSearch-sort-';

	store.connect( this, { update: 'onStoreUpdate' } );
	SortPreference.super.call( this, config );
	this.setValueFromStore();
	this.connect( this, { change: 'onValueUpdate' } );
};

OO.inheritClass( SortPreference, OO.ui.DropdownInputWidget );
OO.mixinClass( SortPreference, ClassesForDropdownOptions );

SortPreference.prototype.onStoreUpdate = function () {
	this.setValueFromStore();
};

SortPreference.prototype.setValueFromStore = function () {
	this.setValue( this.store.getSortMethod() );
};

SortPreference.prototype.onValueUpdate = function () {
	this.store.setSortMethod( this.getValue() );
};

module.exports = SortPreference;
