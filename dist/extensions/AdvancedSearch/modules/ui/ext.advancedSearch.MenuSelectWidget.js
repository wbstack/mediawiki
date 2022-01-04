( function () {
	/**
	 * A floating menu widget for the filter list
	 *
	 * @extends OO.ui.MenuSelectWidget
	 *
	 * @constructor
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} [config] Configuration object
	 * @cfg {jQuery} [$overlay] A jQuery object serving as overlay for popups
	 */
	mw.libs.advancedSearch.ui.MenuSelectWidget = function ( store, config ) {

		config = config || {};
		this.store = store;
		this.config = config;
		this.namespaces = config.namespaces;
		this.options = this.createNamespaceOptions( this.namespaces );
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
		var items = this.options.map( function ( option ) {
			var isDiscussionNamespace = ( Number( option.data ) % 2 );
			// The following classes are used here:
			// * mw-advancedSearch-namespace-0
			// * mw-advancedSearch-namespace-1
			// etc.
			return new mw.libs.advancedSearch.ui.ItemMenuOptionWidget( $.extend( {
				data: option.data,
				label: option.label || option.data,
				classes: [ 'mw-advancedSearch-namespace-' + option.data, !isDiscussionNamespace ? 'mw-advancedSearch-namespace-border' : '' ]
			}, this.config ) );
		} );
		this.addItems( items );
	};

	/**
	 * Create an fields array suitable for menu items
	 *
	 * @param {Object} namespaces namespace id => label
	 * @return {Object[]}
	 */
	mw.libs.advancedSearch.ui.MenuSelectWidget.prototype.createNamespaceOptions = function ( namespaces ) {
		var options = [];
		Object.keys( namespaces ).forEach( function ( id ) {
			options.push( {
				data: id,
				label: namespaces[ id ]
			} );
		} );
		return options;
	};

}() );
