'use strict';

const KNOWN_DOCUMENT_TYPES = [
	'xls', 'xlsx',
	'doc', 'docx',
	'odt',
	'ods',
	'odp',
	'pdf'
];

/**
 * @param {string} fileExtension
 * @return {boolean}
 */
const isKnownDocumentType = function ( fileExtension ) {
	return KNOWN_DOCUMENT_TYPES.indexOf( fileExtension ) !== -1;
};

/**
 * @param {string} mimeType
 * @return {string}
 */
const getTopLevelMimeType = function ( mimeType ) {
	return mimeType.split( '/' )[ 0 ];
};

/**
 * @param {Object.<string,Object[]>} options
 * @param {string} groupName
 * @param {Object} option
 */
const addFileOption = function ( options, groupName, option ) {
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
 * @param {Object.<string,Object[]>} options
 * @param {Object.<string,string>} allowedMimeTypes File extension => MIME type
 * @return {Object.<string,Object[]>}
 */
const getFileOptions = function ( options, allowedMimeTypes ) {
	for ( const fileExtension in allowedMimeTypes ) {
		let groupName = 'other';
		const mimeType = allowedMimeTypes[ fileExtension ],
			topLevelType = getTopLevelMimeType( mimeType );

		if ( isKnownDocumentType( fileExtension ) ) {
			groupName = 'document';
		} else if ( Object.prototype.hasOwnProperty.call( options, topLevelType ) ) {
			groupName = topLevelType;
		}

		addFileOption( options, groupName, { data: mimeType, label: fileExtension } );
	}

	return options;
};

/**
 * @class
 * @property {Object.<string,string>} mimeTypes
 * @property {Object.<string,Object[]>} options
 *
 * @constructor
 * @param {Object.<string,string>} mimeTypes File extension => MIME type
 */
const FileTypeOptionProvider = function ( mimeTypes ) {
	this.mimeTypes = mimeTypes;
	this.options = {
		image: [],
		audio: [],
		video: [],
		document: [],
		other: []
	};
};

OO.initClass( FileTypeOptionProvider );

/**
 * Returns the general file type fields
 *
 * @return {Object[]}
 */
FileTypeOptionProvider.prototype.getFileGroupOptions = function () {
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
FileTypeOptionProvider.prototype.getAllowedFileTypeOptions = function () {
	let options = [];

	// eslint-disable-next-line no-jquery/no-each-util
	$.each( getFileOptions( this.options, this.mimeTypes ), ( index, fileOptions ) => {
		options = options.concat( fileOptions );
	} );

	return options;
};

module.exports = FileTypeOptionProvider;
