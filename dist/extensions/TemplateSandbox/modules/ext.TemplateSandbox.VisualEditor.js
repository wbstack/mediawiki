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

	var apiParams = {
		action: 'parse',
		page: titleInput.getQueryValue(),
		pst: true,
		preview: true,
		templatesandboxtitle: mw.config.get( 'wgPageName' ),
		templatesandboxtext: $( '#wpTextbox1' ).textSelection( 'getContents' ),
		templatesandboxcontentmodel: mw.config.get( 'wgPageContentModel' ),
		disableeditsection: true,
		prop: [ 'text', 'categorieshtml', 'displaytitle' ],
		errorformat: 'html',
		errorlang: mw.config.get( 'wgUserLanguage' ),
		errorsuselocal: true,
		uselang: mw.config.get( 'wgUserLanguage' ),
		useskin: mw.config.get( 'skin' ),
		formatversion: 2
	};

	if ( mw.config.get( 'wgUserVariant' ) ) {
		apiParams.variant = mw.config.get( 'wgUserVariant' );
	}

	api.post( apiParams ).always( function () {
		saveDialog.popPending();
		submitButton.setDisabled( false );
	} ).then( function ( res ) {
		var veConfig = mw.config.get( 'wgVisualEditor' ),
			$heading = $( '<h1>' )
				.addClass( [ 'firstHeading', 'mw-first-heading' ] )
				.html( res.parse.displaytitle ),
			$text = $( '<div>' )
				// The following classes are used here:
				// * mw-content-ltr
				// * mw-content-rtl
				.addClass( [
					'mw-body-content',
					'mw-content-' + veConfig.pageLanguageDir,
					// HACK: T287733
					mw.config.get( 'skin' ) === 'vector' || mw.config.get( 'skin' ) === 'vector-2022' ? 'vector-body' : null
				] )
				.attr( {
					lang: veConfig.pageLanguageCode,
					dir: veConfig.pageLanguageDir
				} )
				.html( res.parse.text );

		saveDialog.setSize( 'full' );
		saveDialog.actions.setMode( 'preview' );
		saveDialog.title.setLabel(
			OO.ui.msg( 'templatesandbox-preview', '', titleInput.getValue() )
		);

		panelLayout.$element.empty().append(
			$( '<div>' ).addClass( 'mw-content-container' ).append(
				$( '<div>' ).addClass( 'mw-body' ).append(
					$heading,
					$text,
					res.parse.categorieshtml
				)
			)
		);
		saveDialog.panels.setItem( panelLayout );

		// Fire hook to allow scripts to process the new content
		mw.hook( 'wikipage.content' ).fire( panelLayout.$element );
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
	new URL( location.href ).searchParams.has( 'wpTemplateSandboxShow' ) ||
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

	mw.hook( 've.deactivationComplete' ).add( function () {
		// Set to undefined so value cannot be restored
		titleInput = undefined;
	} );
}
