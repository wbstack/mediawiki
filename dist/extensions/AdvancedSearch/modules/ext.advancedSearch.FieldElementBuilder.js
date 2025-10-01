'use strict';

/**
 * @class
 *
 * @constructor
 * @param {SearchModel} state
 */
const FieldElementBuilder = function ( state ) {
	this.state = state;
};

Object.assign( FieldElementBuilder.prototype, {
	/**
	 * @type {SearchModel}
	 * @private
	 */
	state: null,

	/**
	 * @param {string} id
	 * @return {Function}
	 * @private
	 */
	createMultiSelectChangeHandler: function ( id ) {
		const self = this;

		return function ( newValue ) {
			if ( typeof newValue !== 'object' ) {
				self.state.storeField( id, newValue );
				return;
			}

			self.state.storeField( id, newValue.map( ( valueObj ) => {
				if ( typeof valueObj === 'string' ) {
					return valueObj;
				}
				return valueObj.data;
			} ) );
		};
	},

	/**
	 * @param {SearchField} field
	 * @return {OO.ui.Widget}
	 * @private
	 */
	createWidget: function ( field ) {
		const widget = field.init( this.state, {
			fieldId: field.id,
			id: 'advancedSearchField-' + field.id
		} );

		if ( !field.customEventHandling ) {
			widget.on( 'change', this.createMultiSelectChangeHandler( field.id ) );
		}

		return widget;
	},

	/**
	 * Build HTML element that contains all the search fields
	 *
	 * @param {FieldCollection} fieldCollection
	 * @return {jQuery} jQuery object that contains all search field widgets, wrapped in Layout widgets
	 */
	buildAllFieldsElement: function ( fieldCollection ) {
		const $allOptions = $( '<div>' ).addClass( 'mw-advancedSearch-fieldContainer' );
		const fieldSets = {};
		const self = this;
		let group;

		fieldCollection.fields.forEach( ( field ) => {
			if ( typeof field.enabled === 'function' && !field.enabled() ) {
				return;
			}

			group = fieldCollection.getGroup( field.id );
			if ( !fieldSets[ group ] ) {
				fieldSets[ group ] = new OO.ui.FieldsetLayout( {
					// The following messages are used here:
					// * advancedsearch-optgroup-text
					// * advancedsearch-optgroup-structure
					// * advancedsearch-optgroup-files
					// * advancedsearch-optgroup-sort
					label: mw.msg( 'advancedsearch-optgroup-' + group )
				} );
			}

			fieldSets[ group ].addItems( [
				field.layout( self.createWidget( field ), field.id, self.state )
			] );
		} );

		for ( group in fieldSets ) {
			$allOptions.append( fieldSets[ group ].$element );
		}

		return $allOptions;
	}
} );

module.exports = FieldElementBuilder;
