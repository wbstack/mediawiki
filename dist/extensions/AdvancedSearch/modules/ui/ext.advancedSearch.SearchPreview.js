'use strict';

/**
 * @param {string} fieldId
 * @return {boolean}
 */
const fieldIsImageDimension = function ( fieldId ) {
	return /^file[hw]$/.test( fieldId );
};

/**
 * @param {string} name
 * @return {string}
 */
const lookupTranslationForSortMethod = function ( name ) {
	// The following messages are used here:
	// * advancedsearch-sort-preview-create-timestamp-asc
	// * advancedsearch-sort-preview-create-timestamp-desc
	// * advancedsearch-sort-preview-last-edit-asc
	// * advancedsearch-sort-preview-last-edit-desc
	// * advancedsearch-sort-preview-relevance
	// * advancedsearch-sort-preview-*
	const msg = mw.message( 'advancedsearch-sort-preview-' + name.replace( /_/g, '-' ) );
	return msg.exists() ? msg.text() : name;
};

/**
 * @param {string} fieldId
 * @return {string}
 */
const lookupTranslationForLabel = function ( fieldId ) {
	// The following messages are used here:
	// * advancedsearch-field-deepcategory
	// * advancedsearch-field-fileh
	// * advancedsearch-field-filetype
	// * advancedsearch-field-filew
	// * advancedsearch-field-hastemplate
	// * advancedsearch-field-inlanguage
	// * advancedsearch-field-intitle
	// * advancedsearch-field-not
	// * advancedsearch-field-or
	// * advancedsearch-field-phrase
	// * advancedsearch-field-plain
	// * advancedsearch-field-sort
	// * advancedsearch-field-subpageof
	return mw.msg( 'advancedsearch-field-' + fieldId );
};

/**
 * @class
 * @extends OO.ui.Widget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} config
 * @param {boolean} [config.data=true] If the set of preview pills should be visible
 * @param {string[]} [config.fieldNames=[]]
 */
const SearchPreview = function ( store, config ) {
	config = Object.assign( {
		data: true
	}, config );
	this.store = store;
	this.fieldNames = config.fieldNames || [];
	this.$previewTagList = $( '<ul>' )
		.addClass( 'mw-advancedSearch-searchPreview-tagList' )
		.attr( 'title', mw.msg( 'advancedsearch-options-pane-preview-list' ) );

	store.connect( this, { update: 'onStoreUpdate' } );

	SearchPreview.super.call( this, config );

	this.$element
		.addClass( 'mw-advancedSearch-searchPreview' )
		.append( this.$previewTagList );
	this.updatePreview();
};

OO.inheritClass( SearchPreview, OO.ui.Widget );

SearchPreview.prototype.onStoreUpdate = function () {
	this.updatePreview();
};

/**
 * Render the preview for all fields
 */
SearchPreview.prototype.updatePreview = function () {
	// TODO check if we really need to re-generate
	this.$previewTagList.find( '.mw-advancedSearch-searchPreview-previewPill' ).remove();

	if ( !this.data ) {
		return;
	}

	this.fieldNames.forEach( ( fieldId ) => {
		const val = this.store.getField( fieldId );

		if ( !this.skipFieldInPreview( fieldId, val ) ) {
			this.$previewTagList.append( this.generateTag( fieldId, val ).$element );
		}
	} );

	this.$previewTagList.append( this.generateTag( 'sort', this.store.getSortMethod() ).$element );
};

/**
 * Decide if an field-value-combination should be listed in the preview
 *
 * @param {string} fieldId
 * @param {string|string[]} value
 * @return {boolean}
 */
SearchPreview.prototype.skipFieldInPreview = function ( fieldId, value ) {
	// No point in previewing empty fields
	if ( !value || ( Array.isArray( value ) && !value.length ) ) {
		return true;
	}

	if ( fieldIsImageDimension( fieldId ) && Array.isArray( value ) && !value[ 1 ] ) {
		return true;
	}

	// We have special handling for sort in #updatePreview
	return fieldId === 'sort';
};

/**
 * Create a tag item that represents the preview for a single field-value-combination
 *
 * @param {string} fieldId
 * @param {string|string[]} value
 * @return {OO.ui.TagItemWidget}
 */
SearchPreview.prototype.generateTag = function ( fieldId, value ) {
	const formattedValue = this.formatValue( fieldId, value );
	let $label = $( '<span>' );
	let disabled = false;
	if ( fieldId === 'sort' ) {
		$label.text( mw.msg( 'advancedsearch-field-preview-sort', formattedValue ) );
		disabled = value === 'relevance';
	} else {
		$label = $label.text( lookupTranslationForLabel( fieldId ) + mw.msg( 'colon-separator' ) )
			// redundant span to cover browsers without support for bdi tag
			.add( $( '<span>' ).addClass( 'mw-advancedSearch-searchPreview-content' ).append(
				$( '<bdi>' ).text( formattedValue )
			) );
	}

	const tag = new OO.ui.TagItemWidget( {
		$element: $( '<li>' ),
		label: $label,
		disabled: disabled,
		draggable: false
	} );

	tag.connect( this, {
		remove: function () {
			this.store.removeField( fieldId );
		}
	} );

	// The following classes are used here:
	// * mw-advancedsearch-searchPreview-preview-deepcategory
	// * mw-advancedsearch-searchPreview-preview-fileh
	// * mw-advancedsearch-searchPreview-preview-filetype
	// * mw-advancedsearch-searchPreview-preview-filew
	// * mw-advancedsearch-searchPreview-preview-hastemplate
	// * mw-advancedsearch-searchPreview-preview-inlanguage
	// * mw-advancedsearch-searchPreview-preview-intitle
	// * mw-advancedsearch-searchPreview-preview-not
	// * mw-advancedsearch-searchPreview-preview-or
	// * mw-advancedsearch-searchPreview-preview-phrase
	// * mw-advancedsearch-searchPreview-preview-plain
	// * mw-advancedsearch-searchPreview-preview-sort
	// * mw-advancedsearch-searchPreview-preview-subpageof
	tag.$element
		.attr( 'title', formattedValue )
		.addClass( 'mw-advancedSearch-searchPreview-previewPill mw-advancedSearch-searchPreview-preview-' + fieldId );

	return tag;
};

/**
 * Format a value to be used in the preview
 *
 * @param {string} fieldId
 * @param {string|string[]} value
 * @return {string}
 */
SearchPreview.prototype.formatValue = function ( fieldId, value ) {
	if ( fieldId === 'filetype' && value.indexOf( '/' ) !== -1 ) {
		const mimeTypes = mw.config.get( 'advancedSearch.mimeTypes' );
		for ( const fileExtension in mimeTypes ) {
			if ( mimeTypes[ fileExtension ] === value ) {
				return fileExtension;
			}
		}
		return value;
	} else if ( fieldIsImageDimension( fieldId ) && Array.isArray( value ) ) {
		return ( value[ 0 ] || '=' ) + mw.msg( 'word-separator' ) + value[ 1 ];
	} else if ( fieldId === 'sort' ) {
		return lookupTranslationForSortMethod( value );
	}

	if ( Array.isArray( value ) ) {
		return value.map( ( v ) => String( v ).trim() ).filter( ( v ) => v !== '' ).join( mw.msg( 'comma-separator' ) );
	}

	return value.trim();
};

/**
 * @param {boolean} show
 */
SearchPreview.prototype.togglePreview = function ( show ) {
	this.data = show;
	this.updatePreview();
};

module.exports = SearchPreview;
