'use strict';

const SearchFieldModule = require( './ext.advancedSearch.SearchField.js' );
const UtilModule = require( './ext.advancedSearch.util.js' );

module.exports = {
	CheckboxInputWidget: require( './ui/ext.advancedSearch.CheckboxInputWidget.js' ),
	ExpandablePane: require( './ui/ext.advancedSearch.ExpandablePane.js' ),
	FieldCollection: require( './ext.advancedSearch.FieldCollection.js' ),
	FieldElementBuilder: require( './ext.advancedSearch.FieldElementBuilder.js' ),
	FormState: require( './ui/ext.advancedSearch.FormState.js' ),
	ItemMenuOptionWidget: require( './ui/ext.advancedSearch.ItemMenuOptionWidget.js' ),
	MenuSelectWidget: require( './ui/ext.advancedSearch.MenuSelectWidget.js' ),
	NamespaceFilters: require( './ui/ext.advancedSearch.NamespaceFilters.js' ),
	NamespacePresetProviders: require( './dm/ext.advancedSearch.NamespacePresetProviders.js' ),
	NamespacePresets: require( './ui/ext.advancedSearch.NamespacePresets.js' ),
	NamespacesPreview: require( './ui/ext.advancedSearch.NamespacesPreview.js' ),
	QueryCompiler: require( './ext.advancedSearch.QueryCompiler.js' ),
	SearchModel: require( './dm/ext.advancedSearch.SearchModel.js' ),
	SearchPreview: require( './ui/ext.advancedSearch.SearchPreview.js' ),
	addDefaultFields: require( './ext.advancedSearch.defaultFields.js' ),
	getDefaultNamespaces: require( './dm/ext.advancedSearch.getDefaultNamespaces.js' ),
	// FIXME: why doesn't "..." lint here?
	SearchField: SearchFieldModule.SearchField,
	createSearchFieldFromObject: SearchFieldModule.createSearchFieldFromObject,
	arrayConcatUnique: UtilModule.arrayConcatUnique,
	arrayContains: UtilModule.arrayContains
};
