( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @class
	 * @extends {OO.ui.Widget}
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.NamespacesPreview = function ( store, config ) {
		config = $.extend( {
			previewOptions: [],
			data: true
		}, config );
		this.store = store;
		this.namespacesLabels = config.namespacesLabels;

		store.connect( this, { update: 'onStoreUpdate' } );

		mw.libs.advancedSearch.ui.NamespacesPreview.parent.call( this, config );

		this.label = new OO.ui.LabelWidget( {
			label: config.label,
			classes: [ 'advancedsearch-namespacesPreview-label' ]
		} );
		this.$element.append( this.label.$element );

		this.$element.addClass( 'mw-advancedSearch-namespacesPreview' );
		this.updatePreview();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.NamespacesPreview, OO.ui.Widget );

	mw.libs.advancedSearch.ui.NamespacesPreview.prototype.onStoreUpdate = function () {
		this.updatePreview();
	};

	/**
	 * Render the preview for all options
	 */
	mw.libs.advancedSearch.ui.NamespacesPreview.prototype.updatePreview = function () {
		// TODO check if we really need to re-generate
		this.$element.find( '.mw-advancedSearch-namespacesPreview-previewPill' ).remove();
		if ( !this.data ) {
			return;
		}
		var namespaces = this.store.getNamespaces();
		namespaces.forEach( function ( nsId ) {
			var val = this.namespacesLabels[ nsId ];
			this.$element.append( this.generateTag( nsId, val ).$element );
		}.bind( this ) );
	};

	/**
	 * Create a tag item that represents the preview for a single option-value-combination
	 *
	 * @param {string} nsId
	 * @param {string} value
	 * @return {OO.ui.TagItemWidget}
	 */
	mw.libs.advancedSearch.ui.NamespacesPreview.prototype.generateTag = function ( nsId, value ) {
		var tag = new OO.ui.TagItemWidget( {
			label: $( '<span>' ).text( value ),
			draggable: false
		} );

		tag.connect( this, {
			remove: function () {
				this.store.removeNamespace( nsId );
			}
		} );

		tag.$element
			.attr( 'title', value )
			.addClass( 'mw-advancedSearch-namespacesPreview-previewPill' );
		return tag;
	};

	mw.libs.advancedSearch.ui.NamespacesPreview.prototype.showPreview = function () {
		this.data = true;
		this.updatePreview();
	};

	mw.libs.advancedSearch.ui.NamespacesPreview.prototype.hidePreview = function () {
		this.data = false;
		this.updatePreview();
	};

}() );
