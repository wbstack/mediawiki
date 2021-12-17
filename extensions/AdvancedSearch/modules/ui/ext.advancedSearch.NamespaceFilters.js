( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	/**
	 * @class
	 * @extends OO.ui.MenuTagMultiselectWidget
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {Object} config
	 * @cfg {Object} [namespaceIcons] Namespace id => icon name
	 * @cfg {Object} [namespaces] Namespace id => Namespace label (similar to mw.config.get( 'wgFormattedNamespaces' ) )
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters = function ( store, config ) {
		config = $.extend( {
			namespaces: {},
			options: [],
			classes: []
		}, config );

		this.store = store;
		this.namespaces = this.prettifyNamespaces( config.namespaces );
		config.classes.push( 'mw-advancedSearch-namespaceFilter' );

		mw.libs.advancedSearch.ui.NamespaceFilters.parent.call( this, $.extend( true, {
			inputPosition: 'outline',
			allowArbitrary: false,
			allowDisplayInvalidTags: false,
			allowReordering: false,
			menu: {
				hideWhenOutOfView: false,
				hideOnChoose: false,
				highlightOnFilter: false,
				namespaces: this.namespaces
			},
			input: {
				icon: 'menu'
			}
		}, config ) );

		this.$namespaceContainer = $( '<span>' ).addClass( 'mw-advancedSearch-namespaceContainer' );
		this.$element.append( this.$namespaceContainer );

		this.store = store;
		this.store.connect( this, { update: 'onStoreUpdate' } );
		this.setValueFromStore();
		this.updateNamespaceFormFields();
		// this needs to be executed last in the stack
		// in order to prevent unwanted scroll-to-top jumps on select
		setTimeout( this.highlightSelectedNamespacesInMenu.bind( this ), 0 );

		this.connect( this, { change: 'onValueUpdate' } );
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.NamespaceFilters, OO.ui.MenuTagMultiselectWidget );

	/**
	 * Prettify namespace names e.g. "config_talk" becomes "Config talk"
	 *
	 * @param {Object} namespaces Namespace id => Namespace label (similar to mw.config.get( 'wgFormattedNamespaces' ) )
	 * @return {Object} namespaces
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.prettifyNamespaces = function ( namespaces ) {
		Object.keys( namespaces ).forEach( function ( id ) {
			namespaces[ id ] = mw.Title.newFromText( namespaces[ id ] ).getMainText();
		} );
		return namespaces;
	};
	/**
	 * @inheritdoc
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.createMenuWidget = function ( menuConfig ) {
		return new mw.libs.advancedSearch.ui.MenuSelectWidget(
			this.store,
			menuConfig
		);
	};

	/**
	 * Update internal state on external updates
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.onStoreUpdate = function () {
		this.setValueFromStore();
		this.updateNamespaceFormFields();
		this.highlightSelectedNamespacesInMenu();
	};

	/**
	 * Update external states on internal updates
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.onValueUpdate = function () {
		this.store.setNamespaces( this.getValue() );
	};

	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.updateNamespaceFormFields = function () {
		var self = this,
			namespaces = this.store.getNamespaces();
		this.$namespaceContainer.empty();
		namespaces.forEach( function ( key ) {
			self.$namespaceContainer.append(
				$( '<input>' ).attr( {
					type: 'hidden',
					value: '1',
					name: 'ns' + key
				} )
			);
		} );
	};

	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.setValueFromStore = function () {
		var self = this,
			namespaces = this.store.getNamespaces();
		// prevent updating the store while reacting to its update notification
		this.disconnect( this, { change: 'onValueUpdate' } );
		this.clearItems();
		namespaces.forEach( function ( key ) {
			self.addTag( key, self.namespaces[ key ] );
		} );

		// re-establish event binding
		this.connect( this, { change: 'onValueUpdate' } );
	};

	/**
	 * Construct a OO.ui.TagItemWidget from given label and data.
	 *
	 * Overrides OO.ui.TagMultiselectWidget default behaviour to further configure individual tags; called in addTag()
	 *
	 * @protected
	 * @param {string} data Item data
	 * @param {string} [label] The label text.
	 * @return {OO.ui.TagItemWidget}
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.createTagItemWidget = function ( data, label ) {
		// The following classes are used here:
		// * mw-advancedSearch-namespace-0
		// * mw-advancedSearch-namespace-1
		// etc.
		return new OO.ui.TagItemWidget( {
			data: data,
			label: label || data,
			draggable: false,
			classes: [ 'mw-advancedSearch-namespace-' + data ]
		} );
	};

	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.highlightSelectedNamespacesInMenu = function () {
		var self = this;
		this.getMenu().getItems().forEach( function ( menuItem ) {
			var isInTagList = !!self.findItemFromData( menuItem.getData() );
			if ( isInTagList ) {
				menuItem.checkboxWidget.setSelected( false );
				menuItem.setSelected( false );
			}
			menuItem.setSelected( isInTagList );
			menuItem.checkboxWidget.setSelected( isInTagList );
		} );
	};

	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.highlightLastSelectedTag = function ( menuItemData ) {
		this.getItems().forEach( function ( tag ) {
			if ( tag.getData() === menuItemData ) {
				tag.$element.addClass( 'selected' );
			}
		} );
	};
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.removeHighlighFromTags = function () {
		this.getItems().forEach( function ( tag ) {
			tag.$element.removeClass( 'selected' );
		} );
	};

	/**
	 * Remove an item from the list of namespaces in store
	 *
	 * @param {string} namespace The item to be removed
	 * @return {string[]} collection of namespaces minus the removed item
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.removeNamespaceTag = function ( namespace ) {
		return this.store.getNamespaces().filter( function ( el ) {
			return el !== namespace;
		} );
	};
	/**
	 * Add or remove a tag for the chosen menu item based on checkbox state
	 *
	 * @param {OO.ui.OptionWidget} menuItem Chosen menu item
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.onMenuChoose = function ( menuItem ) {
		if ( menuItem.checkboxWidget.isSelected() ) {
			this.store.setNamespaces( this.removeNamespaceTag( menuItem.getData() ) );
		} else {
			mw.libs.advancedSearch.ui.NamespaceFilters.parent.prototype.onMenuChoose.call( this, menuItem, true );
			this.highlightLastSelectedTag( menuItem.getData() );
			this.clearInput();
		}
	};

	/**
	 * Handle menu toggle events.
	 *
	 * @private
	 * @param {boolean} isVisible Open state of the menu
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.onMenuToggle = function ( isVisible ) {
		mw.libs.advancedSearch.ui.NamespaceFilters.parent.prototype.onMenuToggle.call( this );
		this.input.setIcon( isVisible ? 'search' : 'menu' );
		if ( !isVisible ) {
			this.removeHighlighFromTags();
		}
	};

	/**
	 * Override to make sure backspace action fills in the pill label rather than the pill data ID
	 *
	 * TODO: Remove this override once OOUI has addressed the issue
	 * Relevant ticket: https://phabricator.wikimedia.org/T190161
	 *
	 * @inheritdoc
	 */
	mw.libs.advancedSearch.ui.NamespaceFilters.prototype.doInputBackspace = function ( e, withMetaKey ) {
		var items, item;
		if (
			this.inputPosition === 'inline' &&
			this.input.getValue() === '' &&
			!this.isEmpty()
		) {
			items = this.getItems();
			item = items[ items.length - 1 ];

			if ( !item.isDisabled() && !item.isFixed() ) {
				this.removeItems( [ item ] );
				if ( !withMetaKey ) {
					/** Change from parent: item.getData() is now item.getLabel() */
					this.input.setValue( item.getLabel() );
				}
			}

			return false;
		}
	};

}() );
