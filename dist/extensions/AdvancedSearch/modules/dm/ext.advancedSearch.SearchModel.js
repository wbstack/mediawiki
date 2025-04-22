'use strict';

const cloneReferenceTypeValue = function ( value ) {
	if ( Array.isArray( value ) ) {
		return value.slice();
	} else if ( value !== null && typeof value === 'object' ) {
		return $.extend( true, {}, value );
	}
	return value;
};

/**
 * @class
 * @mixes OO.EventEmitter
 *
 * @constructor
 * @param {string[]} [defaultNamespaces=[]] The namespaces selected by default (for new searches)
 * @param {Object} [defaultFieldValues={}] Defaults for search field values
 */
const SearchModel = function ( defaultNamespaces, defaultFieldValues ) {
	this.searchFields = {};
	this.namespaces = defaultNamespaces || [];
	this.defaultFieldValues = defaultFieldValues || {};

	// Mixin constructor
	OO.EventEmitter.call( this );
};

/* Initialization */

OO.initClass( SearchModel );
OO.mixinClass( SearchModel, OO.EventEmitter );

/* Events */

/**
 * @event update
 *
 * The state of an option or of the namespaces has changed
 */

/* Constants */

/**
 * Namespace id of File namespace
 *
 * @type {string}
 */
SearchModel.FILE_NAMESPACE = '6';

/* Methods */

/**
 * @param {string} fieldId
 * @param {*} value
 */
SearchModel.prototype.storeField = function ( fieldId, value ) {
	if (
		Object.prototype.hasOwnProperty.call( this.searchFields, fieldId ) &&
		OO.compare( this.searchFields[ fieldId ], value )
	) {
		return;
	}

	if ( value === '' || ( Array.isArray( value ) && value.length === 0 ) ) {
		this.removeField( fieldId );
		return;
	}

	this.searchFields[ fieldId ] = value;

	if ( fieldId === 'filetype' && !this.filetypeSupportsDimensions() ) {
		this.resetFileDimensionFields();
	}

	if ( fieldId === 'filetype' && !this.fileNamespaceIsSelected() ) {
		const namespaces = this.getNamespaces();
		namespaces.push( SearchModel.FILE_NAMESPACE );
		this.setNamespaces( namespaces );
	}

	this.emitUpdate();
};

/**
 * Retrieve value of field with given id
 *
 * @param {string} fieldId
 * @return {*}
 */
SearchModel.prototype.getField = function ( fieldId ) {
	if (
		!Object.prototype.hasOwnProperty.call( this.searchFields, fieldId ) &&
		Object.prototype.hasOwnProperty.call( this.defaultFieldValues, fieldId )
	) {
		return cloneReferenceTypeValue( this.defaultFieldValues[ fieldId ] );
	}
	return cloneReferenceTypeValue( this.searchFields[ fieldId ] );
};

/**
 * Remove field with given id
 *
 * @param {string} fieldId
 */
SearchModel.prototype.removeField = function ( fieldId ) {
	if ( fieldId === 'sort' ) {
		delete this.sortMethod;
	} else {
		delete this.searchFields[ fieldId ];
	}

	if ( fieldId === 'filetype' ) {
		this.resetFileDimensionFields();
	}

	this.emitUpdate();
};

/**
 * Remove namespace with given id
 *
 * @param {string} nsId
 */
SearchModel.prototype.removeNamespace = function ( nsId ) {
	const index = this.namespaces.indexOf( nsId );
	if ( index !== -1 ) {
		this.namespaces.splice( index, 1 );
		this.emitUpdate();
	}
};

/**
 * @param {string} fieldId
 * @param {*} comparisonValue
 * @return {boolean}
 */
SearchModel.prototype.hasFieldChanged = function ( fieldId, comparisonValue ) {
	if (
		!Object.prototype.hasOwnProperty.call( this.searchFields, fieldId ) &&
		Object.prototype.hasOwnProperty.call( this.defaultFieldValues, fieldId )
	) {
		return !OO.compare( this.defaultFieldValues[ fieldId ], comparisonValue );
	}
	return !OO.compare( this.searchFields[ fieldId ], comparisonValue );
};

/**
 * Reset the file dimension search fields
 *
 * @private
 */
SearchModel.prototype.resetFileDimensionFields = function () {
	this.removeField( 'filew' );
	this.removeField( 'fileh' );
};

/**
 * Serialize fields and namespaces to JSON
 *
 * @return {string}
 */
SearchModel.prototype.toJSON = function () {
	if ( $.isEmptyObject( this.searchFields ) ) {
		return '';
	}
	// Warning: While it's possible to change this format (e.g. add elements), please don't make
	// unnecessary changes (e.g. rename or move existing elements). Existing links (e.g. in
	// bookmarks or on wiki pages) won't work as expected any more.
	return JSON.stringify( { fields: this.searchFields } );
};

/**
 * Set fields and namespaces from JSON string
 *
 * @param {string} jsonSerialized
 */
SearchModel.prototype.setAllFromJSON = function ( jsonSerialized ) {
	let unserialized;

	try {
		unserialized = JSON.parse( jsonSerialized );
	} catch ( e ) {
		return;
	}

	if ( !unserialized || typeof unserialized.fields !== 'object' ) {
		return;
	}

	this.searchFields = {};
	for ( const opt in unserialized.fields ) {
		this.searchFields[ opt ] = unserialized.fields[ opt ];
	}
	this.emitUpdate();
};

/**
 * Check if the selected file type supports dimensions
 *
 * @return {boolean}
 */
SearchModel.prototype.filetypeSupportsDimensions = function () {
	const fileType = this.getField( 'filetype' );
	return !!fileType && /^(bitmap|drawing|image|video)\b/.test( fileType );
};

/**
 * @return {boolean}
 */
SearchModel.prototype.fileTypeIsSelected = function () {
	return !!this.getField( 'filetype' );
};

/**
 * @return {boolean}
 */
SearchModel.prototype.fileNamespaceIsSelected = function () {
	return this.namespaces.indexOf( SearchModel.FILE_NAMESPACE ) !== -1;
};

/**
 * @return {string[]}
 */
SearchModel.prototype.getNamespaces = function () {
	return this.namespaces;
};

/**
 * @param {string[]} namespaces
 * @return {string[]}
 */
SearchModel.prototype.sortNamespacesByNumber = function ( namespaces ) {
	return namespaces.sort( ( a, b ) => Number( a ) - Number( b ) );
};

/**
 * @param {string[]} namespaces
 */
SearchModel.prototype.setNamespaces = function ( namespaces ) {
	const previousNamespaces = this.namespaces.slice();

	this.namespaces = this.sortNamespacesByNumber( namespaces );

	if ( !OO.compare( previousNamespaces, this.namespaces ) ) {
		this.emitUpdate();
	}
};

/**
 * @return {string}
 */
SearchModel.prototype.getSortMethod = function () {
	return this.sortMethod || 'relevance';
};

/**
 * @param {string} sortMethod
 */
SearchModel.prototype.setSortMethod = function ( sortMethod ) {
	this.sortMethod = sortMethod;
	this.emitUpdate();
};

SearchModel.prototype.emitUpdate = function () {
	this.emit( 'update' );
};

module.exports = SearchModel;
