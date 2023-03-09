( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * Button that expands a connected pane.
	 *
	 * Both button and pane can have arbitrary jQuery content.
	 *
	 * @class
	 * @extends OO.ui.Widget
	 * @mixes OO.ui.mixin.IndicatorElement
	 * @constructor
	 *
	 * @param {Object} config
	 * @param {string} config.suffix
	 * @param {jQuery} config.$buttonLabel
	 * @param {number} [config.tabIndex]
	 * @param {Function} config.dependentPaneContentBuilder
	 */
	mw.libs.advancedSearch.ui.ExpandablePane = function ( config ) {
		config = $.extend( config, { data: this.STATE_CLOSED } );
		this.suffix = config.suffix;
		mw.libs.advancedSearch.ui.ExpandablePane.parent.call( this, config );

		this.button = new OO.ui.ButtonWidget( {
			classes: [ 'mw-advancedSearch-expandablePane-button' ],
			framed: true,
			tabIndex: config.tabIndex,
			label: config.$buttonLabel,
			indicator: 'down'
		} );
		this.button.connect( this, {
			click: 'onButtonClick'
		} );

		this.$dependentPane = $( '<div>' )
			.attr( 'id', 'mw-advancedSearch-expandable-' + config.suffix )
			.addClass( 'mw-advancedSearch-expandablePane-pane' );
		this.dependentPaneContentBuilder = config.dependentPaneContentBuilder;

		// The following classes are used here:
		// * mw-advancedSearch-expandablePane-namespaces
		// * mw-advancedSearch-expandablePane-options
		this.$element.addClass( 'mw-advancedSearch-expandablePane-' + this.suffix );
		this.$element.append( this.button.$element, this.$dependentPane );
		this.button.$button.attr( {
			'aria-controls': 'mw-advancedSearch-expandable-' + config.suffix,
			'aria-expanded': 'false'
		} );

		this.notifyChildInputVisibility( config.data === this.STATE_OPEN );
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.ExpandablePane, OO.ui.Widget );
	OO.mixinClass( mw.libs.advancedSearch.ui.ExpandablePane, OO.ui.mixin.IndicatorElement );

	mw.libs.advancedSearch.ui.ExpandablePane.prototype.STATE_CLOSED = 'closed';
	mw.libs.advancedSearch.ui.ExpandablePane.prototype.STATE_OPEN = 'open';

	mw.libs.advancedSearch.ui.ExpandablePane.prototype.onButtonClick = function () {
		if ( this.data === this.STATE_OPEN ) {
			this.data = this.STATE_CLOSED;
			this.updatePaneVisibility( this.STATE_CLOSED );
			this.notifyChildInputVisibility( false );
			mw.track( 'counter.MediaWiki.AdvancedSearch.event.' + this.suffix + '.collapse' );
		} else {
			this.data = this.STATE_OPEN;
			this.updatePaneVisibility( this.STATE_OPEN );
			this.notifyChildInputVisibility( true );
			mw.track( 'counter.MediaWiki.AdvancedSearch.event.' + this.suffix + '.expand' );
		}
		this.emit( 'change', this.data );
	};

	mw.libs.advancedSearch.ui.ExpandablePane.prototype.buildDependentPane = function () {
		if ( this.dependentPaneContentBuilder ) {
			this.$dependentPane.append( this.dependentPaneContentBuilder() );
			this.dependentPaneContentBuilder = null;
		}
	};

	/**
	 * @private
	 * @param {boolean} visible
	 */
	mw.libs.advancedSearch.ui.ExpandablePane.prototype.notifyChildInputVisibility = function ( visible ) {
		$( 'input', this.$dependentPane ).trigger( visible === true ? 'visible' : 'hidden' );
	};

	/**
	 * @private
	 * @param {string} state
	 */
	mw.libs.advancedSearch.ui.ExpandablePane.prototype.updatePaneVisibility = function ( state ) {
		if ( state === this.STATE_OPEN ) {
			this.$dependentPane.show();
			this.button.$button.attr( 'aria-expanded', 'true' );
		} else {
			this.$dependentPane.hide();
			this.button.$button.attr( 'aria-expanded', 'false' );
		}
	};

	/**
	 * @return {boolean}
	 */
	mw.libs.advancedSearch.ui.ExpandablePane.prototype.isOpen = function () {
		return this.data === this.STATE_OPEN;
	};

}() );
