/** extensions/TemplateSandbox/modules/ext.TemplateSandbox.preview.js */
/* eslint-disable no-implicit-globals */
/* eslint-disable no-jquery/no-global-selector */
var preview = require( 'mediawiki.page.preview' ),
	parsedMessages = require( './parsedMessages.json' );

/**
 * Modify the config and response objects based on the response.
 *
 * @private
 * @param {Object} config Options for live preview API
 * @param {Object} response Parse API response
 */
function responseHandler( config, response ) {
	var displayTitle = response.parse.displaytitle;

	// Prevent the live preview API from overwriting the heading,
	// which should stay saying "Editing Template:Foo" etc.
	delete response.parse.displaytitle;

	// The default message doesn't use $1, but pass the raw title
	// to be consistent with the server side.
	config.previewHeader = mw.msg( 'templatesandbox-preview', response.parse.title, displayTitle );

	var $previewNote = $( $.parseHTML( parsedMessages[ 'templatesandbox-previewnote' ] ) );

	// Fix the parsed "[[:$1]]".
	$previewNote.filter( 'a' ).add( $previewNote.find( 'a' ) ).filter( function () {
		// Make sure this is the link we're looking for.
		// href and title cannot be relied upon because they vary by whether
		// the page "$1" exists.
		return this.textContent === '$1';
	} ).attr( {
		href: mw.util.getUrl( response.parse.title ),
		class: null,
		title: response.parse.title
	} ).text( response.parse.title );

	// eslint-disable-next-line no-jquery/variable-pattern
	config.previewNote = $previewNote;
}

/**
 * @ignore
 * @param {jQuery.Event} e
 */
function doTemplateSandboxPreview( e ) {
	var $editform = $( '#editform' );

	var promise = preview.doPreview( {
		isLivePreview: true,
		previewHeader: mw.msg( 'preview' ),
		previewNote: parsedMessages.previewnote,
		// This is hidden and identical to wgPageName by default, but that
		// may change in the future, and there already exist user scripts
		// that allow customizing it.
		title: $editform.find( '[name="wpTemplateSandboxTemplate"]' ).val(),
		titleParam: 'templatesandboxtitle',
		textParam: 'templatesandboxtext',
		parseParams: {
			page: $editform.find( '[name="wpTemplateSandboxPage"]' ).val()
		},
		responseHandler: responseHandler,
		createSpinner: true
	} );

	if ( !promise ) {
		// Something has gone wrong, so submit the form the normal way.
		return;
	}

	e.preventDefault();
}

$( function () {
	$( '#wpTemplateSandboxPreview' ).on( 'click', doTemplateSandboxPreview );
} );
