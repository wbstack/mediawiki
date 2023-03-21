( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @class
	 * @extends OO.ui.TagMultiselectWidget
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.ArbitraryWordInput = function ( store, config ) {
		config = $.extend( {}, config );

		this.store = store;
		this.fieldId = config.fieldId;
		this.placeholderText = config.placeholder || '';

		this.store.connect( this, { update: 'onStoreUpdate' } );

		mw.libs.advancedSearch.ui.ArbitraryWordInput.parent.call(
			this,
			$.extend( { allowArbitrary: true }, config )
		);

		this.input.$input.on( 'input', this.buildTagsFromInput.bind( this ) );

		// Optimization: Skip listener if placeholder will always be empty
		if ( this.placeholderText ) {
			this.on( 'change', this.updatePlaceholder.bind( this ) );
		}

		// run initial size calculation after off-canvas construction (hidden parent node)
		this.input.$input.on( 'visible', function () {
			this.updateInputSize();
		}.bind( this ) );

		this.populateFromStore();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.ArbitraryWordInput, OO.ui.TagMultiselectWidget );

	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.populateFromStore = function () {
		if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
			this.setValue( this.store.getField( this.fieldId ) );
		}
	};

	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.onStoreUpdate = function () {
		this.populateFromStore();
	};

	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.buildTagsFromInput = function () {
		var segments = this.input.getValue().split( /[\s,]+/ );

		if ( segments.length > 1 ) {
			var self = this;

			segments.forEach( function ( segment ) {
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
	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.isAllowedData = function ( data ) {
		return data.trim() &&
			mw.libs.advancedSearch.ui.ArbitraryWordInput.parent.prototype.isAllowedData.call( this, data );
	};

	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.updatePlaceholder = function () {
		// bust cached width so placeholder remains changeable
		this.contentWidthWithPlaceholder = undefined;
		this.input.$input.attr( 'placeholder', this.getTextForPlaceholder() );
	};

	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.getTextForPlaceholder = function () {
		return this.getValue().length ? '' : this.placeholderText;
	};

	/**
	 * @inheritdoc
	 */
	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.doInputEnter = function () {
		return !this.input.getValue().trim() ||
			mw.libs.advancedSearch.ui.ArbitraryWordInput.parent.prototype.doInputEnter.call( this );
	};

	/**
	 * @inheritdoc
	 */
	mw.libs.advancedSearch.ui.ArbitraryWordInput.prototype.onInputBlur = function () {
		if ( this.input.getValue().trim() ) {
			this.addTagFromInput();
		}
		return mw.libs.advancedSearch.ui.ArbitraryWordInput.parent.prototype.onInputBlur.call( this );
	};

}() );
