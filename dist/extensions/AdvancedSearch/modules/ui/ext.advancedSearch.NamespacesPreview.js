'use strict';

/**
 * @class
 * @extends OO.ui.Widget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {Object} config
 * @param {boolean} [config.data=true] If the set of preview pills should be visible
 * @param {Object.<int,string>} config.namespacesLabels
 */
const NamespacesPreview = function ( store, config ) {
	config = Object.assign( {
		data: true
	}, config );
	this.store = store;
	this.namespacesLabels = config.namespacesLabels;
	this.$previewTagList = $( '<ul>' )
		.addClass( 'mw-advancedSearch-searchPreview-tagList' )
		.attr( 'title', mw.msg( 'advancedsearch-namespaces-pane-preview-list' ) );

	store.connect( this, { update: 'onStoreUpdate' } );

	NamespacesPreview.super.call( this, config );

	this.$element
		.addClass( 'mw-advancedSearch-namespacesPreview' )
		.append( this.$previewTagList );
	this.updatePreview();
};

OO.inheritClass( NamespacesPreview, OO.ui.Widget );

NamespacesPreview.prototype.onStoreUpdate = function () {
	this.updatePreview();
};

/**
 * Render the preview for all options
 */
NamespacesPreview.prototype.updatePreview = function () {
	// TODO check if we really need to re-generate
	this.$previewTagList.empty();
	if ( !this.data ) {
		return;
	}

	this.store.getNamespaces().forEach( ( nsId ) => {
		const val = this.namespacesLabels[ nsId ] || nsId;
		this.$previewTagList.append( this.generateTag( nsId, val ).$element );
	} );
};

/**
 * Create a tag item that represents the preview for a single option-value-combination
 *
 * @param {string} nsId
 * @param {string} value
 * @return {OO.ui.TagItemWidget}
 */
NamespacesPreview.prototype.generateTag = function ( nsId, value ) {
	const tag = new OO.ui.TagItemWidget( {
		$element: $( '<li>' ),
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

/**
 * @param {boolean} show
 */
NamespacesPreview.prototype.togglePreview = function ( show ) {
	this.data = show;
	this.updatePreview();
};

module.exports = NamespacesPreview;
