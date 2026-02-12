'use strict';

const ItemMenuOptionWidget = require( './ext.advancedSearch.ItemMenuOptionWidget.js' );

/**
 * A floating menu widget for the filter list
 *
 * @class
 * @extends OO.ui.MenuSelectWidget
 *
 * @constructor
 * @param {Object} config
 * @param {Object.<number,string>} config.namespaces
 */
const MenuSelectWidget = function ( config ) {
	MenuSelectWidget.super.call( this, Object.assign( {
		filterFromInput: true
	}, config ) );
	this.addItems( this.createMenuItems( config.namespaces ) );
};

OO.inheritClass( MenuSelectWidget, OO.ui.MenuSelectWidget );

/**
 * @inheritdoc
 */
MenuSelectWidget.prototype.toggle = function ( show ) {
	MenuSelectWidget.super.prototype.toggle.call( this, show );
	this.setVerticalPosition( 'below' );
};

/**
 * @param {Object.<number,string>} namespaces Maps namespace id => label
 * @return {OO.ui.MenuOptionWidget[]}
 */
MenuSelectWidget.prototype.createMenuItems = function ( namespaces ) {
	const items = [];
	for ( const id in namespaces ) {
		const isTalkNamespace = id % 2;
		// The following classes are used here:
		// * mw-advancedSearch-namespace-0
		// * mw-advancedSearch-namespace-1
		// etc.
		items.push( new ItemMenuOptionWidget( {
			data: id,
			label: namespaces[ id ] || id,
			classes: [
				'mw-advancedSearch-namespace-' + id,
				isTalkNamespace ? '' : 'mw-advancedSearch-namespace-border'
			]
		} ) );
	}
	return items;
};

/**
 * @param {string} query
 * @param {string} [mode='prefix']
 * @return {Function}
 */
MenuSelectWidget.prototype.getItemMatcher = function ( query, mode ) {
	if ( mode && mode !== 'prefix' ) {
		// Fall back to the original behavior when not in the default "prefix" mode
		return MenuSelectWidget.super.prototype.getItemMatcher.apply( this, arguments );
	}

	const normalizeForMatching = ( text ) => OO.ui.SelectWidget.static.normalizeForMatching( text )
		// Additional normalization to match the normalization in wgNamespaceIds
		.replace( /[\s_]+/g, '_' );

	const normalizedQuery = normalizeForMatching( query );
	if ( !normalizedQuery ) {
		// Match everything, same default behavior as in OO.ui.SelectWidget.getItemMatcher
		return () => true;
	}

	const goodIds = {};
	// Assume the query was numeric, this just won't do anything in case it was not
	goodIds[ normalizedQuery ] = true;

	const namespaceIds = mw.config.get( 'wgNamespaceIds' );
	for ( const name in namespaceIds ) {
		// Prefix match with the canonical, normalized namespace names in wgNamespaceIds
		if ( name.indexOf( normalizedQuery ) === 0 ) {
			goodIds[ namespaceIds[ name ] ] = true;
		}
	}

	return function ( item ) {
		return item.getData() in goodIds ||
			// This is the default behavior from OO.ui.SelectWidget.getItemMatcher
			normalizeForMatching( item.getMatchText() ).indexOf( normalizedQuery ) === 0;
	};
};

module.exports = MenuSelectWidget;
