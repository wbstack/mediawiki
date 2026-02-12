'use strict';

const { arrayConcatUnique, arrayContains } = require( '../ext.advancedSearch.util.js' );

/**
 * @param {NamespacePresetProviders} presetProvider
 * @param {string[]} namespaceIDs
 * @param {string} presetName
 * @return {boolean}
 */
const validateNamespacePreset = function ( presetProvider, namespaceIDs, presetName ) {
	if ( !presetProvider.namespaceIdsAreValid( namespaceIDs ) ) {
		mw.log.warn( 'AdvancedSearch namespace preset "' + presetName + '" contains unknown namespace id' );
		return false;
	}
	if ( namespaceIDs.length === 0 ) {
		mw.log.warn( 'Empty namespaces for ' + presetName + ' in $wgAdvancedSearchNamespacePresets' );
		return false;
	}
	return true;
};

/**
 * Prepare static namespace id presets for improved performance during later processing
 *
 * @param {Object.<string,Object>} presets
 * @param {NamespacePresetProviders} presetProvider
 * @return {Object.<string,Object>}
 */
const groomPresets = function ( presets, presetProvider ) {
	const groomedPresets = {};
	Object.keys( presets ).forEach( ( key ) => {
		const presetConfig = presets[ key ],
			preset = { label: presetConfig.label || key };

		if ( !presetConfig.enabled ) {
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
				mw.log.warn( 'Provider function ' + presetConfig.provider + ' not registered to NamespacePresetProviders' );
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
};

/**
 * @param {Object.<string,Object>} presets
 * @return {Object}
 */
const prepareOptions = function ( presets ) {
	// eslint-disable-next-line no-jquery/no-map-util
	return $.map( presets, ( preset, id ) => {
		// The following messages are used here:
		// * advancedsearch-namespaces-preset-all
		// * advancedsearch-namespaces-preset-default
		// * advancedsearch-namespaces-preset-general-help
		// * advancedsearch-namespaces-preset-discussion
		const msg = mw.message( preset.label );
		return { data: id, label: msg.exists() ? msg.text() : preset.label };
	} );
};

/**
 * @class
 * @extends OO.ui.CheckboxMultiselectInputWidget
 *
 * @constructor
 * @param {SearchModel} store
 * @param {NamespacePresetProviders} presetProvider
 * @param {Object} config
 * @param {Object.<string,Object>} [config.presets={}]
 */
const NamespacePresets = function ( store, presetProvider, config ) {
	this.store = store;
	this.presets = groomPresets( config.presets || {}, presetProvider );

	config.options = prepareOptions( this.presets );
	NamespacePresets.super.call( this, config );

	// Using undocumented internals because this.on does not work, see https://phabricator.wikimedia.org/T168735
	this.checkboxMultiselectWidget.on( 'change', this.updateStoreFromPresets, [], this );
	this.$element.addClass( 'mw-advancedSearch-namespacePresets' );

	this.updatePresetsFromStore();
	store.connect( this, { update: 'updatePresetsFromStore' } );
};

OO.inheritClass( NamespacePresets, OO.ui.CheckboxMultiselectInputWidget );

/**
 * @param {OO.ui.CheckboxMultioptionWidget} newValue
 */
NamespacePresets.prototype.updateStoreFromPresets = function ( newValue ) {
	const key = newValue.getData();
	if ( newValue.isSelected() ) {
		this.store.setNamespaces( arrayConcatUnique(
			this.presets[ key ].namespaces,
			this.store.getNamespaces() )
		);
	} else {
		this.store.setNamespaces( this.store.getNamespaces().filter( function ( id ) {
			return this.presets[ key ].namespaces.indexOf( id ) === -1;
		}, this ) );
	}
};

NamespacePresets.prototype.updatePresetsFromStore = function () {
	const selectedPresets = {};
	const storeNamespaces = this.store.getNamespaces();
	for ( const key in this.presets ) {
		selectedPresets[ key ] = arrayContains(
			storeNamespaces,
			this.presets[ key ].namespaces
		);
	}
	this.checkboxMultiselectWidget.off( 'change', this.updateStoreFromPresets, this );
	for ( const key in selectedPresets ) {
		const presetWidget = this.checkboxMultiselectWidget.findItemFromData( key ),
			isSelected = selectedPresets[ key ];
		if ( presetWidget.isSelected() !== isSelected ) {
			presetWidget.setSelected( isSelected );
		}
	}
	this.checkboxMultiselectWidget.on( 'change', this.updateStoreFromPresets, [], this );
};

module.exports = NamespacePresets;
