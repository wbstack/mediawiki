'use strict';

/**
 * FieldLayout that can show and hide itself when the store changes, based on visibility function
 *
 * @class
 * @extends OO.ui.FieldLayout
 *
 * @constructor
 * @param {SearchModel} store
 * @param {OO.ui.Widget} widget
 * @param {Object} config
 * @param {Function} config.checkVisibility A callback that returns false when this element should
 *  be hidden
 */
const ImageDimensionLayout = function ( store, widget, config ) {
	this.store = store;
	this.checkVisibility = config.checkVisibility;

	store.connect( this, { update: 'onStoreUpdate' } );

	ImageDimensionLayout.super.call( this, widget, config );

	// Set ARIA labels and description from the FieldLayout label and help text.
	this.fieldWidget.operatorInput.dropdownWidget.$handle.attr( {
		'aria-labelledby': this.$label.attr( 'id' ),
		'aria-describedby': this.$help.attr( 'aria-owns' )
	} );
	this.fieldWidget.valueInput.$input.attr( 'aria-labelledby', this.$label.attr( 'id' ) );

	this.toggle( this.checkVisibility() );
};

OO.inheritClass( ImageDimensionLayout, OO.ui.FieldLayout );

ImageDimensionLayout.prototype.onStoreUpdate = function () {
	this.toggle( this.checkVisibility() );
};

module.exports = ImageDimensionLayout;
