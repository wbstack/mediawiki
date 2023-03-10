/**
 * @class HelpButtonView
 * Module containing presentation logic for the helper button
 */
var HelpButtonView = {

	/**
	 * Renders the help button and renders and adds the popup for it.
	 *
	 * @return {jQuery} the help button object
	 */
	render: function () {
		var helpButton = new OO.ui.ButtonWidget( {
			icon: 'helpNotice',
			framed: false,
			classes: [ 'mw-revslider-show-help' ]
		} );
		var helpPopup = new OO.ui.PopupWidget( {
			$content: $( '<p>' ).text( mw.msg( 'revisionslider-show-help-tooltip' ) ),
			$floatableContainer: helpButton.$element,
			width: 200,
			classes: [ 'mw-revslider-tooltip', 'mw-revslider-help-tooltip' ]
		} );
		helpButton.connect( this, {
			click: 'showDialog'
		} );
		helpButton.$element
			.on( 'mouseover', function () {
				helpPopup.toggle( true );
			} )
			.on( 'mouseout', function () {
				helpPopup.toggle( false );
			} )
			.children().attr( {
				'aria-haspopup': 'true',
				'aria-label': mw.msg( 'revisionslider-show-help-tooltip' )
			} );

		$( 'body' ).append( helpPopup.$element );

		return helpButton.$element;
	},

	showDialog: function () {
		require( './ext.RevisionSlider.HelpDialog.js' ).show();
	}
};

module.exports = HelpButtonView;
