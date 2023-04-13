( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	// Internal constants
	var FILETYPES_WITH_DIMENSIONS = [
		'bitmap',
		'drawing',
		'image',
		'video'
	];

	var cloneReferenceTypeValue = function ( value ) {
		if ( Array.isArray( value ) ) {
			return value.slice();
		} else if ( value !== null && typeof value === 'object' ) {
			return $.extend( true, {}, value );
		}
		return value;
	};

	/**
	 * @class
	 * @constructor
	 * @mixes OO.EventEmitter
	 * @param {string[]} [defaultNamespaces=[]] The namespaces selected by default (for new searches)
	 * @param {Object} [defaultFieldValues={}] Defaults for search field values
	 */
	mw.libs.advancedSearch.dm.SearchModel = function ( defaultNamespaces, defaultFieldValues ) {
		this.searchFields = {};
		this.namespaces = defaultNamespaces || [];
		this.defaultFieldValues = defaultFieldValues || {};

		// Mixin constructor
		OO.EventEmitter.call( this );
	};

	/* Initialization */

	OO.initClass( mw.libs.advancedSearch.dm.SearchModel );
	OO.mixinClass( mw.libs.advancedSearch.dm.SearchModel, OO.EventEmitter );

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
	mw.libs.advancedSearch.dm.SearchModel.FILE_NAMESPACE = '6';

	/* Methods */

	/**
	 * @param {string} fieldId
	 * @param {*} value
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.storeField = function ( fieldId, value ) {
		var namespaces;

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

		namespaces = this.getNamespaces();
		if ( fieldId === 'filetype' && namespaces.indexOf( mw.libs.advancedSearch.dm.SearchModel.FILE_NAMESPACE ) === -1 ) {
			namespaces.push( mw.libs.advancedSearch.dm.SearchModel.FILE_NAMESPACE );
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
	mw.libs.advancedSearch.dm.SearchModel.prototype.getField = function ( fieldId ) {
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
	mw.libs.advancedSearch.dm.SearchModel.prototype.removeField = function ( fieldId ) {
		if ( !Object.prototype.hasOwnProperty.call( this.searchFields, fieldId ) ) {
			return;
		}

		delete this.searchFields[ fieldId ];

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
	mw.libs.advancedSearch.dm.SearchModel.prototype.removeNamespace = function ( nsId ) {
		var index = this.getNamespaces().indexOf( nsId );
		if ( index !== -1 ) {
			this.getNamespaces().splice( index, 1 );
		}
		this.emitUpdate();
	};

	/**
	 * @param {string} fieldId
	 * @param {*} comparisonValue
	 * @return {boolean}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.hasFieldChanged = function ( fieldId, comparisonValue ) {
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
	mw.libs.advancedSearch.dm.SearchModel.prototype.resetFileDimensionFields = function () {
		this.removeField( 'filew' );
		this.removeField( 'fileh' );
	};

	/**
	 * Serialize fields and namespaces to JSON
	 *
	 * @return {string}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.toJSON = function () {
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
	mw.libs.advancedSearch.dm.SearchModel.prototype.setAllFromJSON = function ( jsonSerialized ) {
		var unserialized;

		try {
			unserialized = JSON.parse( jsonSerialized );
		} catch ( e ) {
			return;
		}

		if ( !unserialized || typeof unserialized.fields !== 'object' ) {
			return;
		}

		this.searchFields = {};
		for ( var opt in unserialized.fields ) {
			this.searchFields[ opt ] = unserialized.fields[ opt ];
		}
		this.emitUpdate();
	};

	/**
	 * Check if the selected file type supports dimensions
	 *
	 * @return {boolean}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.filetypeSupportsDimensions = function () {
		var fileType = this.getField( 'filetype' );
		if ( !fileType ) {
			return false;
		}
		var generalFileType = fileType.replace( /\/.*/, '' );
		return FILETYPES_WITH_DIMENSIONS.indexOf( generalFileType ) !== -1;
	};

	/**
	 * @return {boolean}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.fileTypeIsSelected = function () {
		return !!this.getField( 'filetype' );
	};

	/**
	 * @return {boolean}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.fileNamespaceIsSelected = function () {
		return this.getNamespaces().indexOf( mw.libs.advancedSearch.dm.SearchModel.FILE_NAMESPACE ) === -1;
	};

	/**
	 * @return {string[]}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.getNamespaces = function () {
		return this.namespaces;
	};

	/**
	 * @param {string[]} namespaces
	 * @return {string[]}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.sortNamespacesByNumber = function ( namespaces ) {
		return namespaces.sort( function ( a, b ) {
			return Number( a ) - Number( b );
		} );
	};

	/**
	 * @param {string[]} namespaces
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.setNamespaces = function ( namespaces ) {
		var previousNamespaces = this.namespaces.slice( 0 );

		this.namespaces = this.sortNamespacesByNumber( namespaces );

		if ( !OO.compare( previousNamespaces, this.namespaces ) ) {
			this.emitUpdate();
		}
	};

	/**
	 * @return {string}
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.getSortMethod = function () {
		return this.sortMethod || 'relevance';
	};

	/**
	 * @param {string} sortMethod
	 */
	mw.libs.advancedSearch.dm.SearchModel.prototype.setSortMethod = function ( sortMethod ) {
		this.sortMethod = sortMethod;
		this.emitUpdate();
	};

	mw.libs.advancedSearch.dm.SearchModel.prototype.emitUpdate = function () {
		this.emit( 'update' );
	};

}() );
