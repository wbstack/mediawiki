( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.ui = mw.libs.advancedSearch.ui || {};

	function validateNamespacePreset( presetProvider, namespaceIDs, presetName ) {
		if ( !presetProvider.namespaceIdsAreValid( namespaceIDs ) ) {
			mw.log.warn( 'AdvancedSearch namespace preset "' + presetName + '" contains unknown namespace ID' );
			return false;
		}
		if ( namespaceIDs.length === 0 ) {
			mw.log.warn( 'Empty namespaces for ' + presetName + ' in $wgAdvancedSearchNamespacePresets' );
			return false;
		}
		return true;
	}

	/**
	 * Prepare static namespace ID presets for improved performance during later processing
	 *
	 * @param {Object} presets
	 * @param {mw.libs.advancedSearch.dm.NamespacePresetProviders} presetProvider
	 * @return {Object}
	 */
	function groomPresets( presets, presetProvider ) {
		var groomedPresets = {};
		Object.keys( presets ).forEach( function ( key ) {
			var presetConfig = presets[ key ],
				preset = { label: presetConfig.label || key };

			if ( !Object.prototype.hasOwnProperty.call( presetConfig, 'enabled' ) || presetConfig.enabled !== true ) {
				return;
			}

			if ( typeof presetConfig.provider !== 'undefined' ) {
				if ( presetProvider.hasProvider( presetConfig.provider ) ) {
					preset.namespaces = presetProvider.getNamespaceIdsFromProvider( presetConfig.provider );
					// Providers might return empty arrays to disable certain presets when preconditions are not fulfilled
					if ( preset.namespaces.length === 0 ) {
						return;
					}
				} else {
					mw.log.warn( 'Provider function ' + presetConfig.provider + ' not registered to mw.libs.advancedSearch.dm.NamespacePresetProviders' );
					return;
				}
			} else if ( Array.isArray( presetConfig.namespaces ) ) {
				if ( !validateNamespacePreset( presetProvider, presetConfig.namespaces, key ) ) {
					return;
				}
				preset.namespaces = presetConfig.namespaces;
			} else {
				mw.log.warn( 'No defined namespaces or provider function for ' + key + ' in $wgAdvancedSearchNamespacePresets' );
				return;
			}
			preset.namespaces.sort();
			groomedPresets[ key ] = preset;
		} );

		return groomedPresets;
	}

	/**
	 * @param {Object} presets
	 * @return {Object}
	 */
	function prepareOptions( presets ) {
		// eslint-disable-next-line no-jquery/no-map-util
		return $.map( presets, function ( preset, id ) {
			// The following messages are used here:
			// * advancedsearch-namespaces-preset-all
			// * advancedsearch-namespaces-preset-default
			// * advancedsearch-namespaces-preset-general-help
			// * advancedsearch-namespaces-preset-discussion
			return { data: id, label: mw.msg( preset.label ) };
		} );
	}

	/**
	 * @class
	 * @extends {OO.ui.CheckboxMultiselectInputWidget}
	 * @constructor
	 *
	 * @param {mw.libs.advancedSearch.dm.SearchModel} store
	 * @param {mw.libs.advancedSearch.dm.NamespacePresetProviders} presetProvider
	 * @param {Object} config
	 */
	mw.libs.advancedSearch.ui.NamespacePresets = function ( store, presetProvider, config ) {
		config = $.extend( {
			presets: {}
		}, config );
		config.presets = groomPresets( config.presets, presetProvider );

		config.options = prepareOptions( config.presets );
		this.store = store;

		this.presets = config.presets;

		mw.libs.advancedSearch.ui.NamespacePresets.parent.call( this, config );

		// Using undocumented internals because this.on does not work, see https://phabricator.wikimedia.org/T168735
		this.checkboxMultiselectWidget.on( 'change', this.updateStoreFromPresets, [], this );
		this.$element.addClass( 'mw-advancedSearch-namespacePresets' );

		this.updatePresetsFromStore();
		store.connect( this, { update: 'updatePresetsFromStore' } );
	};

	OO.inheritClass( mw.libs.advancedSearch.ui.NamespacePresets, OO.ui.CheckboxMultiselectInputWidget );

	mw.libs.advancedSearch.ui.NamespacePresets.prototype.updateStoreFromPresets = function ( newValue ) {
		var key = newValue.getData();
		if ( newValue.selected ) {
			this.store.setNamespaces( mw.libs.advancedSearch.util.arrayConcatUnique(
				this.presets[ key ].namespaces,
				this.store.getNamespaces() )
			);
		} else {
			this.store.setNamespaces( this.store.getNamespaces().filter( function ( id ) {
				return this.presets[ key ].namespaces.indexOf( id ) === -1;
			}, this ) );
		}
	};

	mw.libs.advancedSearch.ui.NamespacePresets.prototype.updatePresetsFromStore = function () {
		var selectedPresets = {},
			self = this,
			storeNamespaces = self.store.getNamespaces();
		Object.keys( this.presets ).forEach( function ( key ) {
			selectedPresets[ key ] = mw.libs.advancedSearch.util.arrayContains(
				storeNamespaces,
				self.presets[ key ].namespaces
			);
		} );
		this.checkboxMultiselectWidget.off( 'change', this.updateStoreFromPresets, this );
		Object.keys( selectedPresets ).forEach( function ( key ) {
			var presetWidget = self.checkboxMultiselectWidget.findItemFromData( key ),
				isSelected = selectedPresets[ key ];
			if ( presetWidget.isSelected() !== isSelected ) {
				presetWidget.setSelected( isSelected );
			}
		} );
		this.checkboxMultiselectWidget.on( 'change', this.updateStoreFromPresets, [], this );
	};

}() );
