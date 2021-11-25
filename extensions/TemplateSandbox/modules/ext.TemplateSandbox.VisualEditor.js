/* eslint-disable no-implicit-globals */
/* eslint-disable no-jquery/no-global-selector */

var titleInput, submitButton, actionField, api, panelLayout;

function showPreview() {
	var saveDialog = ve.init.target.saveDialog;

	saveDialog.pushPending();
	submitButton.setDisabled( true );

	if ( !api ) {
		api = new mw.Api();
	}

	api.post( {
		action: 'parse',
		page: titleInput.getQueryValue(),
		templatesandboxtitle: mw.config.get( 'wgPageName' ),
		templatesandboxtext: $( '#wpTextbox1' ).textSelection( 'getContents' ),
		templatesandboxcontentmodel: mw.config.get( 'wgPageContentModel' ),
		errorformat: 'html',
		errorlang: mw.config.get( 'wgUserLanguage' ),
		errorsuselocal: true,
		formatversion: 2
	} ).always( function () {
		saveDialog.popPending();
		submitButton.setDisabled( false );
	} ).then( function ( res ) {
		saveDialog.setSize( 'full' );
		saveDialog.actions.setMode( 'preview' );
		saveDialog.title.setLabel(
			OO.ui.msg( 'templatesandbox-preview', '', titleInput.getValue() )
		);
		panelLayout.$element.html( res.parse.text );
		saveDialog.panels.setItem( panelLayout );
	}, function ( code, data ) {
		var msg;
		switch ( code ) {
			case 'missingtitle':
				msg = OO.ui.msg( 'templatesandbox-editform-title-not-exists' );
				break;
			case 'invalidtitle':
				msg = OO.ui.msg( 'templatesandbox-editform-invalid-title' );
				break;
			default:
				msg = api.getErrorMessage( data );
				break;
		}
		saveDialog.showErrors( new OO.ui.Error( msg, { recoverable: false } ) );
	} );
}

if (
	( new mw.Uri() ).query.wpTemplateSandboxShow !== undefined ||
	require( './namespaces.json' ).indexOf( mw.config.get( 'wgNamespaceNumber' ) ) !== -1
) {
	mw.hook( 've.saveDialog.stateChanged' ).add( function () {
		if ( $( '#wpTextbox1' ).length && !$( '#templatesandbox-editform' ).length ) {
			titleInput = new mw.widgets.TitleInputWidget( {
				placeholder: OO.ui.msg( 'templatesandbox-editform-page-label' ),
				value: titleInput ? titleInput.getValue() : undefined
			} );
			submitButton = new OO.ui.ButtonWidget( {
				label: OO.ui.msg( 'templatesandbox-editform-view-label' )
			} );
			actionField = new OO.ui.ActionFieldLayout( titleInput, submitButton, {
				align: 'top',
				label: mw.message( 'templatesandbox-editform-legend' ).parseDom()
			} );
			panelLayout = new OO.ui.PanelLayout( {
				expanded: false,
				padded: true
			} );

			submitButton.on( 'click', showPreview );
			titleInput.on( 'enter', showPreview );
			actionField.$element.attr( 'id', 'templatesandbox-editform' );

			ve.init.target.saveDialog.panels.addItems( [ panelLayout ] );
			ve.init.target.saveDialog.$saveOptions.after( actionField.$element );
		}
	} );

	mw.hook( 've.deactivate' ).add( function () {
		// Set to undefined so value cannot be restored
		titleInput = undefined;
	} );
}
