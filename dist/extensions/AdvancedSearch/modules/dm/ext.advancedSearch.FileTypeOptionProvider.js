( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	var KNOWN_DOCUMENT_TYPES = [
		'xls', 'xlsx',
		'doc', 'docx',
		'odt',
		'ods',
		'odp',
		'pdf'
	];

	var isKnownDocumentType = function ( fileExtension ) {
		return KNOWN_DOCUMENT_TYPES.indexOf( fileExtension ) !== -1;
	};

	/**
	 * @param {string} mimeType
	 * @return {string}
	 */
	var getTopLevelMimeType = function ( mimeType ) {
		return mimeType.split( '/' )[ 0 ];
	};

	/**
	 * @param {Object} options
	 * @param {string} groupName
	 * @param {Object} option
	 */
	var addFileOption = function ( options, groupName, option ) {
		if ( options[ groupName ].length === 0 ) {
			// The following messages are used here:
			// * advancedsearch-filetype-section-types
			// * advancedsearch-filetype-section-image
			// * advancedsearch-filetype-section-video
			// * advancedsearch-filetype-section-audio
			// * advancedsearch-filetype-section-document
			// * advancedsearch-filetype-section-other
			options[ groupName ] = [ { optgroup: mw.msg( 'advancedsearch-filetype-section-' + groupName ) } ];
		}
		options[ groupName ] = options[ groupName ].concat( option );
	};

	/**
	 * @param {Object} options
	 * @param {Object} allowedMimeTypes File extension => MIME type
	 * @return {Object}
	 */
	var getFileOptions = function ( options, allowedMimeTypes ) {
		Object.keys( allowedMimeTypes ).forEach( function ( fileExtension ) {
			var groupName = 'other',
				mimeType = allowedMimeTypes[ fileExtension ],
				topLevelType = getTopLevelMimeType( mimeType );

			if ( isKnownDocumentType( fileExtension ) ) {
				groupName = 'document';
			} else if ( Object.prototype.hasOwnProperty.call( options, topLevelType ) ) {
				groupName = topLevelType;
			}

			addFileOption( options, groupName, { data: mimeType, label: fileExtension } );
		} );

		return options;
	};

	/**
	 * @class
	 * @constructor
	 *
	 * @param {Object} mimeTypes File extension => MIME type
	 */
	mw.libs.advancedSearch.dm.FileTypeOptionProvider = function ( mimeTypes ) {
		this.mimeTypes = mimeTypes;
		this.options = {
			image: [],
			audio: [],
			video: [],
			document: [],
			other: []
		};
	};

	OO.initClass( mw.libs.advancedSearch.dm.FileTypeOptionProvider );

	/**
	 * Returns the general file type fields
	 *
	 * @return {Object[]}
	 */
	mw.libs.advancedSearch.dm.FileTypeOptionProvider.prototype.getFileGroupOptions = function () {
		return [
			{ optgroup: mw.msg( 'advancedsearch-filetype-section-types' ) },
			{ data: 'bitmap', label: mw.msg( 'advancedsearch-filetype-bitmap' ) },
			{ data: 'drawing', label: mw.msg( 'advancedsearch-filetype-drawing' ) },
			{ data: 'video', label: mw.msg( 'advancedsearch-filetype-video' ) },
			{ data: 'audio', label: mw.msg( 'advancedsearch-filetype-audio' ) },
			{ data: 'office', label: mw.msg( 'advancedsearch-filetype-office' ) }
		];
	};

	/**
	 * Returns the file type fields based on allowed mime types
	 *
	 * @return {Object[]}
	 */
	mw.libs.advancedSearch.dm.FileTypeOptionProvider.prototype.getAllowedFileTypeOptions = function () {
		var options = [];

		// eslint-disable-next-line no-jquery/no-each-util
		$.each( getFileOptions( this.options, this.mimeTypes ), function ( index, fileOptions ) {
			options = options.concat( fileOptions );
		} );

		return options;
	};

}() );
