/**
 * Module containing the SplitTwoColConflict tour
 *
 * @param {OO.ui.WindowManager} windowManager
 * @param {Object} config
 * @param {string} config.header for the initial dialog window
 * @param {string} config.image.css class for the initial dialog window
 * @param {string} config.image.height css value for image
 * @param {string} config.message for the initial dialog window
 * @param {string} config.close button text for the dialog window
 * @constructor
 */
const Tour = function ( windowManager, config ) {
	this.windowManager = windowManager;
	this.config = config;
};

Object.assign( Tour.prototype, {

	/**
	 * @type {OO.ui.Dialog}
	 */
	dialog: null,

	/**
	 * @type {OO.ui.WindowManager}
	 */
	windowManager: null,

	/**
	 * @type {Object}
	 */
	config: null,

	/**
	 * @type {Object[]}
	 */
	buttons: [],

	/**
	 * Creates the initial dialog window
	 */
	createDialog: function () {
		const self = this;

		function TourDialog( config ) {
			this.panel = config.panel;
			TourDialog.super.call( this, config );
		}

		OO.inheritClass( TourDialog, OO.ui.Dialog );

		TourDialog.static.name = 'TourDialog';
		TourDialog.prototype.initialize = function () {
			TourDialog.super.prototype.initialize.call( this );
			this.content = new OO.ui.PanelLayout( { padded: true, expanded: false } );
			this.content.$element.addClass( 'mw-twocolconflict-split-tour-intro-container' );
			this.content.$element.append( this.panel );
			this.$body.append( this.content.$element );
		};

		const closeButton = new OO.ui.ButtonWidget( {
			label: this.config.close,
			flags: [ 'primary', 'progressive' ]
		} );

		const $panel = $( '<div>' )
			.append(
				$( '<h5>' )
					.text( this.config.header )
					.addClass( 'mw-twocolconflict-split-tour-intro-container-header' )
			)
			.append(
				// The following classes are used here:
				// * mw-twocolconflict-split-tour-image-dual-column-view-1
				// * mw-twocolconflict-split-tour-image-single-column-view-1
				$( '<div>' )
					.addClass( 'mw-twocolconflict-split-tour-image-landscape ' + this.config.image.css )
					// Todo: find a better way to handle image scaling
					.css( 'height', this.config.image.height )
			)
			.append(
				$( '<p>' ).text( this.config.message )
			);

		// As of now this is never empty, but if it is hinting at the blue dots doesn't make sense
		if ( this.buttons.length ) {
			$panel.append(
				$( '<div>' )
					.addClass( 'mw-twocolconflict-split-tour-intro-container-blue-dot-hint' )
					.append(
						$( '<div>' ).addClass( 'mw-twocolconflict-split-tour-image-blue-dot' ),
						$( '<p>' ).text( mw.msg( 'twocolconflict-split-tour-dialog-dot-message' ) )
					)
			);
		}

		$panel.append( closeButton.$element );

		this.dialog = new TourDialog( {
			size: 'large',
			panel: $panel
		} );

		closeButton.on( 'click', () => {
			self.dialog.close();
			self.showButtons();
		} );

		$( 'body' ).append( this.windowManager.$element );
		this.windowManager.addWindows( [ this.dialog ] );
	},

	/**
	 * @param {jQuery} $element
	 * @return {jQuery}
	 */
	createPopupButton: function ( $element ) {
		return $( '<div>' )
			.addClass( 'mw-pulsating-dot mw-twocolconflict-split-tour-pulsating-button' )
			.appendTo( $element )
			.hide();
	},

	/**
	 * @param {string} header
	 * @param {string} message
	 * @param {jQuery} $pulsatingButton
	 * @return {OO.ui.PopupWidget}
	 */
	createPopup: function ( header, message, $pulsatingButton ) {
		const self = this;

		const closeButton = new OO.ui.ButtonWidget( {
			label: mw.msg( 'twocolconflict-split-tour-popup-btn-text' ),
			flags: [ 'primary', 'progressive' ]
		} );

		const $content = $( '<div>' )
			.append( $( '<h5>' ).text( header ) )
			.append( $( '<p>' ).html( message ) );

		const popup = new OO.ui.PopupWidget( {
			position: 'below',
			align: 'forwards',
			$content: $content,
			$footer: closeButton.$element,
			padded: true,
			width: 450,
			classes: [ 'mw-twocolconflict-split-tour-popup' ]
		} );

		closeButton.on( 'click', () => {
			popup.toggle( false );
		} );

		$pulsatingButton.on( 'click', ( e ) => {
			e.preventDefault();
			$pulsatingButton.hide();
			self.showTourPopup( popup );
		} );

		return popup;
	},

	showButtons: function () {
		const self = this;

		this.buttons.forEach( ( data ) => {
			if ( !data.popup ) {
				data.$pulsatingButton = self.createPopupButton( data.$element );
				data.popup = self.createPopup(
					data.header, data.message, data.$pulsatingButton
				);
				data.$element.append( data.popup.$element );
			}

			data.$pulsatingButton.show();

			if ( data.showByDefault ) {
				// Later, manual clicks should not trigger this auto-open action again
				data.showByDefault = false;
				data.$pulsatingButton.click();
			}
		} );
	},

	/**
	 * Adds a tutorial step to the tour, this includes a popup and a button
	 *
	 * @param {string} header for the popup
	 * @param {string} message for the popup
	 * @param {jQuery} $element to which the popup should be anchored to
	 * @param {boolean} [showByDefault=false] whether the popup should be shown by default
	 */
	addTourPopup: function ( header, message, $element, showByDefault ) {
		this.buttons.push( {
			header: header,
			message: message,
			$element: $element,
			showByDefault: showByDefault || false
		} );
	},

	showTourPopup: function ( popup ) {
		this.buttons.forEach( ( data ) => {
			if ( data.popup ) {
				data.popup.toggle( data.popup === popup );
			}
		} );
	},

	hideTourPopups: function () {
		this.buttons.forEach( ( data ) => {
			if ( data.popup ) {
				data.popup.toggle( false );
				data.$pulsatingButton.hide();
			}
		} );
	},

	/**
	 * @param {string[]} buttonClasses classes for the help button
	 * @return {OO.ui.ButtonWidget}
	 */
	getHelpButton: function ( buttonClasses ) {
		const self = this;

		// The following classes are used here:
		// * mw-twocolconflict-split-tour-help-button
		// * mw-twocolconflict-split-tour-help-button-single-column-view
		const helpButton = new OO.ui.ButtonWidget( {
			icon: 'info',
			framed: false,
			title: mw.msg( 'twocolconflict-split-help-tooltip' ),
			classes: buttonClasses
		} );

		helpButton.on( 'click', () => {
			self.showTour();
		} );

		return helpButton.$element;
	},

	showTour: function () {
		this.hideTourPopups();

		if ( !this.dialog ) {
			this.createDialog();
		}
		this.windowManager.openWindow( this.dialog );
	}
} );

function isSingleColumnView() {
	return $( 'input[name="mw-twocolconflict-single-column-view"]' ).val() === '1';
}

/**
 * Initializes the tour
 */
function initialize() {
	const $body = $( 'body' );
	const Settings = require( '../ext.TwoColConflict.Settings.js' );
	const settings = new Settings();
	const windowManager = new OO.ui.WindowManager();
	let tour;
	let hideDialogSetting;

	if ( isSingleColumnView() ) {
		hideDialogSetting = 'hide-help-dialogue-single-column-view';

		tour = new Tour(
			windowManager,
			{
				header: mw.msg( 'twocolconflict-split-tour-dialog-header-single-column-view' ),
				image: {
					css: 'mw-twocolconflict-split-tour-image-single-column-view-1',
					height: '240px'
				},
				message: mw.msg( 'twocolconflict-split-tour-dialog-message-single-column-view' ),
				close: mw.msg( 'twocolconflict-split-tour-dialog-btn-text-single-column-view' )
			}
		);

		$( '.firstHeading' ).append(
			tour.getHelpButton( [ 'mw-twocolconflict-split-tour-help-button-single-column-view' ] )
		);
	} else {
		hideDialogSetting = 'hide-help-dialogue';

		tour = new Tour(
			windowManager,
			{
				header: mw.msg( 'twocolconflict-split-tour-dialog-header' ),
				image: {
					css: 'mw-twocolconflict-split-tour-image-dual-column-view-1',
					height: '200px'
				},
				message: mw.msg( 'twocolconflict-split-tour-dialog-message' ),
				close: mw.msg( 'twocolconflict-split-tour-dialog-btn-text' )
			}
		);

		tour.addTourPopup(
			mw.msg( 'twocolconflict-split-tour-popup1-header' ),
			mw.msg( 'twocolconflict-split-tour-popup1-message' ),
			$body.find( '.mw-twocolconflict-split-your-version-header' ),
			true
		);

		tour.addTourPopup(
			mw.msg( 'twocolconflict-split-tour-popup2-header' ),
			mw.msg( 'twocolconflict-split-tour-popup2-message' ),
			$body.find( '.mw-twocolconflict-split-selection-row' ).first()
		);

		tour.addTourPopup(
			mw.msg( 'twocolconflict-split-tour-popup3-header' ),
			mw.msg( 'twocolconflict-split-tour-popup3-message' ),
			$body.find( '.mw-twocolconflict-diffchange' ).first()
		);

		$( '.mw-twocolconflict-split-flex-header' ).append(
			tour.getHelpButton( [ 'mw-twocolconflict-split-tour-help-button' ] )
		);
	}

	if ( !settings.loadBoolean( hideDialogSetting, false ) ) {
		tour.showTour();
		settings.saveBoolean( hideDialogSetting, true );
	}
}

module.exports = initialize;
