( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * Combination widget that consists of an "comparison operator" dropdown and a size input field.
	 *
	 * Use it to search for image/video heights and widths.
	 *
	 * @class
	 * @extends {OO.ui.Widget}
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.ImageDimensionInput = function ( store, config ) {
		this.fieldId = config.fieldId;
		this.store = store;
		store.connect( this, { update: 'onStoreUpdate' } );

		mw.libs.advancedSearch.ui.ImageDimensionInput.parent.call( this, config );

		if ( !Array.isArray( this.data ) ) {
			this.data = [ '>', '' ];
		}

		this.$element.addClass( 'mw-advancedSearch-filesize' );

		this.operatorInput = new OO.ui.DropdownInputWidget( {
			options: [
				{ data: '', label: mw.msg( 'advancedsearch-filesize-equals' ) },
				{ data: '>', label: mw.msg( 'advancedsearch-filesize-greater-than' ) },
				{ data: '<', label: mw.msg( 'advancedsearch-filesize-smaller-than' ) }
			],
			value: '>'
		} );
		this.valueInput = new OO.ui.TextInputWidget( { label: 'px' } );

		this.operatorInput.connect( this, { change: 'onOperatorInputChange' } );
		this.valueInput.connect( this, { change: 'onValueInputChange' } );

		this.$element.append(
			$( '<div>' ).addClass( 'operator-container' ).append( this.operatorInput.$element )
		);
		this.$element.append(
			$( '<div>' ).addClass( 'value-container' ).append( this.valueInput.$element )
		);

		this.operatorInput.setValue( '>' ); // Workaround for broken default value, see https://phabricator.wikimedia.org/T166783
		this.setValuesFromStore();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.ImageDimensionInput, OO.ui.Widget );

	mw.libs.advancedSearch.ui.ImageDimensionInput.prototype.onOperatorInputChange = function () {
		this.data = [ this.operatorInput.getValue(), this.data[ 1 ] ];
		this.emit( 'change', this.data );
	};

	mw.libs.advancedSearch.ui.ImageDimensionInput.prototype.onValueInputChange = function () {
		this.data = [ this.data[ 0 ], this.valueInput.getValue() ];
		this.emit( 'change', this.data );
	};

	mw.libs.advancedSearch.ui.ImageDimensionInput.prototype.onStoreUpdate = function () {
		this.setValuesFromStore();
	};

	mw.libs.advancedSearch.ui.ImageDimensionInput.prototype.setValuesFromStore = function () {
		if ( this.store.hasFieldChanged( this.fieldId, this.data ) ) {
			this.setValue( this.store.getField( this.fieldId ) );
		}
	};

	/**
	 * @return {Array}
	 */
	mw.libs.advancedSearch.ui.ImageDimensionInput.prototype.getValue = function () {
		return this.data;
	};

	mw.libs.advancedSearch.ui.ImageDimensionInput.prototype.setValue = function ( newValue ) {
		this.data = newValue;
		this.operatorInput.setValue( this.data[ 0 ] );
		this.valueInput.setValue( this.data[ 1 ] );
	};

	/**
	 * @inheritdoc
	 */
	mw.libs.advancedSearch.ui.ImageDimensionInput.prototype.simulateLabelClick = function () {
		this.operatorInput.focus();
	};

}() );
