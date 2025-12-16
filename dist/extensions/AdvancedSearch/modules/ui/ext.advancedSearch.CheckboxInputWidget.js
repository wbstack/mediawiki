'use strict';

/**
 * A widget representing a single toggle filter
 *
 * @class
 * @extends OO.ui.CheckboxInputWidget
 *
 * @constructor
 * @param {Object} [config={}]
 */
const CheckboxInputWidget = function ( config ) {
	CheckboxInputWidget.super.call( this, config );
	// This checkbox is fake and used only for visual purposes.
	// Event handling is done for the entire menu item element in NamespaceFilters
	this.$input
		.on( 'click', false );
};

OO.inheritClass( CheckboxInputWidget, OO.ui.CheckboxInputWidget );

module.exports = CheckboxInputWidget;
