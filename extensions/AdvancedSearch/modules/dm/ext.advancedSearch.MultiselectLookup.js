( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	var markNonExistent = function ( item ) {
		item.$label.addClass( 'new' );
	};

	var markPageExistence = function ( item, queryCache ) {
		if ( queryCache.get( item.getLabel() ) === 'NO' ) {
			markNonExistent( item );
		}
	};

	var populateCache = function ( res, self ) {
		var pages = [];

		// eslint-disable-next-line no-jquery/no-each-util
		$.each( res.query.pages, function ( index, page ) {
			if ( !page.missing ) {
				pages.push( page.title );
				self.queryCache.set( page.title, 'YES' );
				return;
			}
			self.queryCache.set( page.title, 'NO' );
		} );

		return pages;
	};

	/**
	 * @param {string} name
	 * @param {string} namespace
	 * @return {mw.Title|null}
	 */
	var getTitle = function ( name, namespace ) {
		return mw.Title.newFromText( name, mw.config.get( 'wgNamespaceIds' )[ namespace ] );
	};

	mw.libs.advancedSearch.dm.MultiselectLookup = function ( store, config ) {
		config = $.extend( {}, config, {
			allowArbitrary: true,
			input: {
				autocomplete: false
			}
		} );
		this.store = store;
		this.fieldId = config.fieldId;
		this.lookupId = config.lookupId;
		this.api = config.api || new mw.Api();
		this.queryCache = new mw.libs.advancedSearch.dm.TitleCache();

		this.store.connect( this, { update: 'onStoreUpdate' } );

		mw.libs.advancedSearch.dm.MultiselectLookup.parent.call( this, config );

		this.$input = this.input.$input;

		this.input.connect( this, { change: 'onLookupInputChange' } );

		OO.ui.mixin.LookupElement.call( this, config );

		this.populateFromStore();
		this.connect( this, { change: 'onValueUpdate' } );
	};

	OO.inheritClass( mw.libs.advancedSearch.dm.MultiselectLookup, OO.ui.TagMultiselectWidget );
	OO.mixinClass( mw.libs.advancedSearch.dm.MultiselectLookup, OO.ui.mixin.LookupElement );

	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.onStoreUpdate = function () {
		this.populateFromStore();
	};

	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.populateFromStore = function () {

		if ( this.store.hasFieldChanged( this.fieldId, this.getValue() ) ) {
			this.setValue( this.store.getField( this.fieldId ) );
		}
	};

	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.setValue = function ( valueObject ) {
		var names = Array.isArray( valueObject ) ? valueObject : [ valueObject ];
		// Initialize with "PENDING" value to avoid new request in createTagItemWidget
		names.forEach( function ( value ) {
			this.queryCache.set( value, 'PENDING' );
		}.bind( this ) );
		mw.libs.advancedSearch.dm.MultiselectLookup.parent.prototype.setValue.call( this, valueObject );

		this.searchForPagesInNamespace( names ).then( function () {
			var self = this;
			this.items.forEach( function ( item ) {
				markPageExistence( item, self.queryCache );
			} );
		}.bind( this ) );
	};

	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.searchForPageInNamespace = function ( name ) {
		var deferred = $.Deferred(),
			self = this;

		var title = getTitle( name, this.lookupId );
		if ( !title ) {
			this.queryCache[ name ] = 'NO';
			return deferred.resolve( [] ).promise();
		}

		this.queryCache[ name ] = 'PENDING';

		this.api.get( {
			formatversion: 2,
			action: 'query',
			prop: 'info',
			titles: title.getPrefixedText()
		} ).done( function ( res ) {
			var pages = populateCache( res, self );
			deferred.resolve( pages );
		} ).fail( function () {
			deferred.reject.bind( deferred );
		} );

		return deferred.promise();
	};

	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.searchForPagesInNamespace = function ( names ) {
		var deferred = $.Deferred(),
			self = this;

		names = names.map( function ( name ) {
			var title = getTitle( name, self.lookupId );
			if ( !title ) {
				this.queryCache[ name ] = 'NO';
				return null;
			}
			return title.getPrefixedText();
		} ).filter( function ( name ) {
			return name !== null;
		} );
		if ( names.length === 0 ) {
			return deferred.resolve( [] ).promise();
		}

		this.api.get( {
			formatversion: 2,
			action: 'query',
			prop: 'info',
			titles: names.join( '|' )
		} ).done( function ( res ) {
			var pages = [];
			populateCache( res, self );
			deferred.resolve( pages );
		} ).fail( function () {
			deferred.reject.bind( deferred );
		} );

		return deferred.promise();
	};

	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.createTagItemWidget = function ( data, label ) {
		label = label || data;
		var title = getTitle( label, this.lookupId ),
			$tagItemLabel = $( '<a>' ),
			tagItem;

		if ( title ) {
			$tagItemLabel.attr( {
				target: '_blank',
				href: title.getUrl(),
				title: title.getPrefixedText()
			} );
		}

		tagItem = new OO.ui.TagItemWidget( {
			data: data,
			label: label,
			$label: $tagItemLabel
		} );

		if ( !this.queryCache.has( tagItem.getLabel() ) ) {
			this.searchForPageInNamespace( tagItem.getLabel() )
				.then( function ( response ) {
					if ( response.length === 0 ) {
						markNonExistent( tagItem );
					}
				} );
		} else {
			markPageExistence( tagItem, this.queryCache );
		}

		// The click event defined in TagItemWidget's constructor
		// is removed because it destroys the pill field on click.
		tagItem.$element.off( 'click' );

		return tagItem;
	};

	/**
	 * Update external states on internal updates
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.onValueUpdate = function () {
		this.store.storeField( this.fieldId, this.getValue() );
	};

	/**
	 * @inheritdoc OO.ui.mixin.LookupElement
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.getLookupRequest = function () {
		var value = this.input.getValue();

		// @todo More elegant way to prevent empty API requests?
		if ( value.trim() === '' ) {
			return $.Deferred().reject();
		}
		return this.api.get( {
			action: 'opensearch',
			search: this.input.getValue(),
			namespace: mw.config.get( 'wgNamespaceIds' )[ this.lookupId ]
		} );
	};

	/**
	 * @inheritdoc OO.ui.mixin.LookupElement
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.getLookupCacheDataFromResponse = function ( response ) {
		return response || [];
	};

	/**
	 * @inheritdoc OO.ui.mixin.LookupElement
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.getLookupMenuOptionsFromData = function ( data ) {
		var
			items = [],
			i, pageNameWithoutNamespace,
			currentValues = this.getValue();
		for ( i = 0; i < data[ 1 ].length; i++ ) {
			pageNameWithoutNamespace = this.removeNamespace( data[ 1 ][ i ] );

			// do not show suggestions for items already selected
			if ( currentValues.indexOf( pageNameWithoutNamespace ) !== -1 ) {
				continue;
			}

			items.push( new OO.ui.MenuOptionWidget( {
				data: pageNameWithoutNamespace,
				label: pageNameWithoutNamespace
			} ) );
		}
		return items;
	};

	/**
	 * Get the name part of a page title containing a namespace
	 *
	 * @param {string} pageTitle
	 * @return {string}
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.removeNamespace = function ( pageTitle ) {
		return mw.Title.newFromText( pageTitle ).getMainText();
	};

	/**
	 * Override behavior from OO.ui.mixin.LookupElement
	 *
	 * @param {OO.ui.TagItemWidget} item
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.onLookupMenuChoose = function ( item ) {
		this.addTag( item.getData() );
		this.input.setValue( '' );
	};

	/**
	 * Override to make sure query caching is based on the correct (input) value
	 *
	 * @inheritdoc
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.getRequestQuery = function () {
		return this.input.getValue();
	};

	/**
	 * Implemented because OO.ui.mixin.LookupElement expects it.
	 *
	 * @return {boolean}
	 */
	mw.libs.advancedSearch.dm.MultiselectLookup.prototype.isReadOnly = function () {
		return false;
	};

}() );
