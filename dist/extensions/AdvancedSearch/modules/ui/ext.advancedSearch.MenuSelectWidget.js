( function () {
	/**
	 * A floating menu widget for the filter list
	 *
	 * @extends OO.ui.MenuSelectWidget
	 *
	 * @constructor
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 * @cfg {Object} namespaces
	 * @cfg {jQuery} [$overlay] A jQuery object serving as overlay for popups
	 */
	mw.libs.advancedSearch.ui.MenuSelectWidget = function ( store, config ) {
		this.store = store;
		this.config = config;
		this.namespaces = config.namespaces;
		this.$overlay = config.$overlay || this.$element;
		this.$body = $( '<div>' ).addClass( 'mw-advancedSearch-ui-menuSelectWidget-body' );

		mw.libs.advancedSearch.ui.MenuSelectWidget.parent.call( this, $.extend( {
			$autoCloseIgnore: this.$overlay,
			filterFromInput: true
		}, config ) );
		this.createMenu();
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.MenuSelectWidget, OO.ui.MenuSelectWidget );

	/**
	 * @inheritdoc
	 */
	mw.libs.advancedSearch.ui.MenuSelectWidget.prototype.toggle = function ( show ) {
		mw.libs.advancedSearch.ui.MenuSelectWidget.parent.prototype.toggle.call( this, show );
		this.setVerticalPosition( 'below' );
	};

	mw.libs.advancedSearch.ui.MenuSelectWidget.prototype.createMenu = function () {
		if ( this.menuInitialized ) {
			return;
		}
		this.menuInitialized = true;
		var items = [];
		for ( var id in this.namespaces ) {
			var isDiscussionNamespace = ( Number( id ) % 2 );
			// The following classes are used here:
			// * mw-advancedSearch-namespace-0
			// * mw-advancedSearch-namespace-1
			// etc.
			items.push( new mw.libs.advancedSearch.ui.ItemMenuOptionWidget( $.extend( {
				data: id,
				label: this.namespaces[ id ] || id,
				classes: [
					'mw-advancedSearch-namespace-' + id,
					!isDiscussionNamespace ? 'mw-advancedSearch-namespace-border' : ''
				]
			}, this.config ) ) );
		}
		this.addItems( items );
	};

}() );
