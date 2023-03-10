/**
 * Module containing presentation logic for the revision pointers
 *
 * @class PointerView
 * @param {Pointer} pointer
 * @param {string} name
 * @constructor
 */
function PointerView( pointer, name ) {
	this.pointer = pointer;
	this.name = name;
}

$.extend( PointerView.prototype, {
	/**
	 * @type {string}
	 */
	name: '',

	/**
	 * @type {Pointer}
	 */
	pointer: null,

	/**
	 * @type {jQuery}
	 */
	$html: null,

	/**
	 * Initializes the DOM element
	 */
	initialize: function () {
		// eslint-disable-next-line mediawiki/class-doc
		this.$html = $( '<div>' )
			.addClass( 'mw-revslider-pointer mw-revslider-pointer-cursor ' + this.name );
	},

	/**
	 * @return {jQuery}
	 */
	render: function () {
		this.initialize();
		return this.getElement();
	},

	/**
	 * @return {jQuery}
	 */
	getElement: function () {
		return this.$html;
	},

	/**
	 * Returns whether a pointer is the newer revision pointer based on its CSS class
	 *
	 * @return {boolean}
	 */
	isNewerPointer: function () {
		return this.getElement().hasClass( 'mw-revslider-pointer-newer' );
	},

	/**
	 * Returns the offset (margin-left) depending on whether its the newer or older pointer
	 *
	 * @return {number}
	 */
	getOffset: function () {
		return this.isNewerPointer() ? 16 : 0;
	},

	// For correct positioning of the pointer in the RTL mode the left position is flipped in the container.
	// 30 pixel have to be added to cover the arrow and its margin.
	getAdjustedLeftPositionWhenRtl: function ( pos ) {
		return this.getElement().offsetParent().width() - pos - 30;
	},

	/**
	 * Sets the HTML attribute for the position
	 *
	 * @param {number} pos
	 */
	setDataPositionAttribute: function ( pos ) {
		if ( this.getElement() === null ) {
			this.initialize();
		}
		this.getElement().attr( 'data-pos', pos );
	},

	/**
	 * Moves the pointer to a position
	 *
	 * @param {number} posInPx
	 * @param {number} revisionWidth
	 * @param {number} [baseDuration] Duration per revisionWidth, is adjusted by log() distance
	 * @return {jQuery}
	 */
	animateTo: function ( posInPx, revisionWidth, baseDuration ) {
		var animatePos = { left: posInPx },
			currentPos = this.getElement().position();

		baseDuration = typeof baseDuration !== 'undefined' ? baseDuration : 100;
		if ( this.getElement().css( 'direction' ) === 'rtl' ) {
			animatePos.left = this.getAdjustedLeftPositionWhenRtl( animatePos.left );
		}
		var distance = Math.abs( animatePos.left - currentPos.left ) / revisionWidth;
		var duration = baseDuration * Math.log( 5 + distance );
		return this.getElement().animate( animatePos, duration, 'linear' );
	},

	/**
	 * Slides the pointer to the revision it's pointing at
	 *
	 * @param {Slider} slider
	 * @param {number} [duration]
	 * @return {jQuery}
	 */
	slideToPosition: function ( slider, duration ) {
		var relativePos = this.pointer.getPosition() - slider.getOldestVisibleRevisionIndex();
		return this.animateTo( ( relativePos - 1 ) * slider.getView().revisionWidth, slider.getView().revisionWidth, duration );
	},

	/**
	 * Slides the pointer to the side of the slider when it's not in the current range of revisions
	 *
	 * @param {Slider} slider
	 * @param {boolean} posBeforeSlider
	 * @param {number} [duration]
	 * @return {jQuery}
	 */
	slideToSide: function ( slider, posBeforeSlider, duration ) {
		if ( posBeforeSlider ) {
			return this.animateTo( this.getOffset() - 2 * slider.getView().revisionWidth, slider.getView().revisionWidth, duration );
		} else {
			return this.animateTo( slider.getRevisionsPerWindow() * slider.getView().revisionWidth + this.getOffset(), slider.getView().revisionWidth, duration );
		}
	},

	/**
	 * Decides based on its position whether the pointer should be sliding to the side or to its position
	 *
	 * @param {Slider} slider
	 * @param {number} [duration]
	 * @return {jQuery}
	 */
	slideToSideOrPosition: function ( slider, duration ) {
		var firstVisibleRev = slider.getOldestVisibleRevisionIndex(),
			posBeforeSlider = this.pointer.getPosition() < firstVisibleRev,
			isVisible = !posBeforeSlider && this.pointer.getPosition() <= firstVisibleRev + slider.getRevisionsPerWindow();
		if ( isVisible ) {
			return this.slideToPosition( slider, duration );
		} else {
			return this.slideToSide( slider, posBeforeSlider, duration );
		}
	}
} );

module.exports = PointerView;
