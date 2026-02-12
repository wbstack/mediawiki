'use strict';

/**
 * @class
 * @extends OO.ui.TagMultiselectWidget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} config
 * @param {string} config.fieldId Field name
 * @param {string} [config.placeholder=""]
 */
const ArbitraryWordInput = function ( store, config ) {
	this.store = store;
	this.fieldId = config.fieldId;
	this.placeholderText = config.placeholder || '';

	this.store.connect( this, { update: 'onStoreUpdate' } );

	ArbitraryWordInput.super.call(
		this,
		Object.assign( { allowArbitrary: true }, config )
	);

	this.input.$input.on( 'input', this.buildTagsFromInput.bind( this ) );

	// Optimization: Skip listener if placeholder will always be empty
	if ( this.placeholderText ) {
		this.on( 'change', this.updatePlaceholder.bind( this ) );
	}

	// run initial size calculation after off-canvas construction (hidden parent node)
	this.input.$input.on( 'visible', () => {
		this.updateInputSize();
	} );

	this.populateFromStore();
};

OO.inheritClass( ArbitraryWordInput, OO.ui.TagMultiselectWidget );

ArbitraryWordInput.prototype.populateFromStore = function () {
	if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
		this.setValue( this.store.getField( this.fieldId ) );
	}
};

ArbitraryWordInput.prototype.onStoreUpdate = function () {
	this.populateFromStore();
};

ArbitraryWordInput.prototype.buildTagsFromInput = function () {
	const segments = this.input.getValue().split( /[\s,]+/ );

	if ( segments.length > 1 ) {
		const self = this;

		segments.forEach( ( segment ) => {
			if ( self.isAllowedData( segment ) ) {
				self.addTag( segment );
			}
		} );

		this.clearInput();
	}
};

/**
 * @inheritdoc
 */
ArbitraryWordInput.prototype.isAllowedData = function ( data ) {
	return data.trim() &&
		ArbitraryWordInput.super.prototype.isAllowedData.call( this, data );
};

ArbitraryWordInput.prototype.updatePlaceholder = function () {
	// bust cached width so placeholder remains changeable
	this.contentWidthWithPlaceholder = undefined;
	this.input.$input.attr( 'placeholder', this.getTextForPlaceholder() );
};

/**
 * @return {string}
 */
ArbitraryWordInput.prototype.getTextForPlaceholder = function () {
	return this.getValue().length ? '' : this.placeholderText;
};

/**
 * @inheritdoc
 */
ArbitraryWordInput.prototype.doInputEnter = function () {
	return !this.input.getValue().trim() ||
		ArbitraryWordInput.super.prototype.doInputEnter.call( this );
};

/**
 * @inheritdoc
 */
ArbitraryWordInput.prototype.onInputBlur = function () {
	if ( this.input.getValue().trim() ) {
		this.addTagFromInput();
	}
	return ArbitraryWordInput.super.prototype.onInputBlur.call( this );
};

module.exports = ArbitraryWordInput;
