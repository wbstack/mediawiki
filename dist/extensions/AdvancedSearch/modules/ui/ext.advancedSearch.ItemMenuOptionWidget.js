'use strict';

const CheckboxInputWidget = require( './ext.advancedSearch.CheckboxInputWidget.js' );

/**
 * A menu option widget that shows the selection state with a checkbox.
 *
 * @class
 * @extends OO.ui.MenuOptionWidget
 *
 * @constructor
 * @param {Object} config
 * @param {string} config.data Value associated with this item, usually the namespace id
 */
const ItemMenuOptionWidget = function ( config ) {
	const $label = $( '<div>' )
		.addClass( 'mw-advancedSearch-ui-itemMenuOptionWidget-label' );

	ItemMenuOptionWidget.super.call( this, Object.assign( {
		// Override the 'check' icon that OOUI defines
		icon: ''
	}, config ) );

	this.checkboxWidget = new CheckboxInputWidget( {
		value: config.data
	} );
	$label.append(
		$( '<div>' )
			.addClass( 'mw-advancedSearch-ui-itemMenuOptionWidget-label-title' )
			.append( $( '<bdi>' ).append( this.$label ) )
	);

	const layout = new OO.ui.FieldLayout( this.checkboxWidget, {
		label: $label,
		align: 'inline'
	} );

	// HACK: Prevent defaults on 'click' for the label so it
	// doesn't steal the focus away from the input. This means
	// we can continue arrow-movement after we click the label
	// and is consistent with the checkbox *itself* also preventing
	// defaults on 'click' as well.
	layout.$label.on( 'click', false );

	this.$element
		.addClass( 'mw-advancedSearch-ui-itemMenuOptionWidget' )
		.append(
			$( '<div>' )
				.addClass( 'mw-advancedSearch-ui-cell mw-advancedSearch-ui-itemMenuOptionWidget-itemCheckbox' )
				.append( layout.$element )
		);
};

OO.inheritClass( ItemMenuOptionWidget, OO.ui.MenuOptionWidget );

// prevents a visual jump when selecting a menu option
ItemMenuOptionWidget.static.scrollIntoViewOnSelect = false;

module.exports = ItemMenuOptionWidget;
