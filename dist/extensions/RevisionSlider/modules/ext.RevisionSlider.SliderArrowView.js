/**
 * Module containing presentation logic for the arrow buttons
 *
 * @class SliderArroView
 * @param {SliderView} sliderView
 * @constructor
 */
function SliderArrowView( sliderView ) {
	this.sliderView = sliderView;
}

$.extend( SliderArrowView.prototype, {
	/**
	 * @type {SliderView}
	 */
	sliderView: null,

	/**
	 * Renders the backwards arrow button, returns it
	 * and renders and adds the popup for it.
	 *
	 * @return {OO.ui.ButtonWidget}
	 */
	renderBackwardArrow: function () {
		var backwardArrowButton = new OO.ui.ButtonWidget( {
			icon: 'previous',
			width: 20,
			height: 140,
			framed: true,
			classes: [ 'mw-revslider-arrow', 'mw-revslider-arrow-backwards' ]
		} );

		var backwardArrowPopup = new OO.ui.PopupWidget( {
			$content: $( '<p>' ).text( mw.msg( 'revisionslider-arrow-tooltip-older' ) ),
			$floatableContainer: backwardArrowButton.$element,
			width: 200,
			classes: [ 'mw-revslider-tooltip', 'mw-revslider-arrow-tooltip' ]
		} );

		backwardArrowButton.connect( this, {
			click: [ 'arrowClickHandler', backwardArrowButton ]
		} );

		backwardArrowButton.$element
			.attr( 'data-dir', -1 )
			.children().attr( 'aria-label', mw.msg( 'revisionslider-arrow-tooltip-older' ) )
			.on( 'mouseover', { button: backwardArrowButton, popup: backwardArrowPopup }, this.showPopup )
			.on( 'mouseout', { popup: backwardArrowPopup }, this.hidePopup )
			.on( 'focusin', { button: backwardArrowButton }, this.arrowFocusHandler );

		$( 'body' ).append( backwardArrowPopup.$element );

		return backwardArrowButton;
	},

	/**
	 * Renders the forwards arrow button, returns it
	 * and renders and adds the popup for it.
	 *
	 * @return {OO.ui.ButtonWidget}
	 */
	renderForwardArrow: function () {
		var forwardArrowButton = new OO.ui.ButtonWidget( {
			icon: 'next',
			width: 20,
			height: 140,
			framed: true,
			classes: [ 'mw-revslider-arrow', 'mw-revslider-arrow-forwards' ]
		} );

		var forwardArrowPopup = new OO.ui.PopupWidget( {
			$content: $( '<p>' ).text( mw.msg( 'revisionslider-arrow-tooltip-newer' ) ),
			$floatableContainer: forwardArrowButton.$element,
			width: 200,
			classes: [ 'mw-revslider-tooltip', 'mw-revslider-arrow-tooltip' ]
		} );

		forwardArrowButton.connect( this, {
			click: [ 'arrowClickHandler', forwardArrowButton ]
		} );

		forwardArrowButton.$element
			.attr( 'data-dir', 1 )
			.children().attr( 'aria-label', mw.msg( 'revisionslider-arrow-tooltip-newer' ) )
			.on( 'mouseover', { button: forwardArrowButton, popup: forwardArrowPopup }, this.showPopup )
			.on( 'mouseout', { popup: forwardArrowPopup }, this.hidePopup )
			.on( 'focusin', { button: forwardArrowButton }, this.arrowFocusHandler );

		$( 'body' ).append( forwardArrowPopup.$element );

		return forwardArrowButton;
	},

	showPopup: function ( e ) {
		var button = e.data.button,
			popup = e.data.popup;
		if ( typeof button !== 'undefined' && button.isDisabled() ) {
			return;
		}
		popup.$element.css( {
			left: $( this ).offset().left + $( this ).outerWidth() / 2 + 'px',
			top: $( this ).offset().top + $( this ).outerHeight() + 'px'
		} );
		popup.toggle( true );
	},

	hidePopup: function ( e ) {
		var popup = e.data.popup;
		popup.toggle( false );
	},

	/**
	 * @param {OO.ui.ButtonWidget} button
	 */
	arrowClickHandler: function ( button ) {
		if ( button.isDisabled() ) {
			return;
		}
		mw.track( 'counter.MediaWiki.RevisionSlider.event.arrowClick' );
		this.sliderView.slideView( button.$element.data( 'dir' ) );
	},

	/**
	 * Disabled oo.ui.ButtonWidgets get focused when clicked. In particular cases
	 * (arrow gets clicked when disabled, none other elements gets focus meanwhile, the other arrow is clicked)
	 * previously disabled arrow button still has focus and has OOUI focused button styles
	 * applied (blue border) which is not what is wanted. And generally setting a focus on disabled
	 * buttons does not seem right in case of RevisionSlider's arrow buttons.
	 * This method removes focus from the disabled button if such case happens.
	 *
	 * @param {jQuery.Event} e
	 */
	arrowFocusHandler: function ( e ) {
		var button = e.data.button;
		if ( button.isDisabled() ) {
			button.$element.find( 'a.oo-ui-buttonElement-button' ).trigger( 'blur' );
		}
	}
} );

module.exports = SliderArrowView;
