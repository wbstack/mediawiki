/* eslint-disable no-jquery/no-global-selector */
'use strict';

const {
	ExpandablePane,
	FieldCollection,
	FieldElementBuilder,
	FormState,
	NamespaceFilters,
	NamespacePresetProviders,
	NamespacePresets,
	NamespacesPreview,
	QueryCompiler,
	SearchModel,
	SearchPreview,
	addDefaultFields,
	getDefaultNamespaces
} = require( 'ext.advancedSearch.elements' );

/**
 * It is possible for the namespace field to be completely empty
 * and at the same time have the file type option selected.
 * This would lead to an empty search result in most cases,
 * as the default namespaces (which are used when no namespaces are selected) do not contain files.
 * As a courtesy to the user, we're forcefully re-adding the file namespace.
 * When the search result page loads the file namespace will show up in the selected namespace list.
 *
 * @param {jQuery} $searchField The search fields inside the forms
 * @param {SearchModel} state
 */
const forceFileTypeNamespaceWhenSearchForFileType = function ( $searchField, state ) {
	if ( state.fileTypeIsSelected() && !state.fileNamespaceIsSelected() ) {
		// Can't call state.setNamespaces with file namespace here,
		// because this function is called inside the onSubmit event
		// and the DOM update from the state change would take too long.
		$searchField.after( $( '<input>' ).prop( {
			name: 'ns6',
			type: 'hidden'
		} ).val( '1' ) );
	}
};

/**
 * @param {jQuery} $search The search form element
 * @param {jQuery} $searchField The search fields inside the forms
 * @param {SearchModel} state
 * @param {QueryCompiler} queryCompiler
 */
const setSearchSubmitTrigger = function ( $search, $searchField, state, queryCompiler ) {
	$search.on( 'submit', function () {
		// T354107: This can actually trigger more than once; undo before we try again
		$searchField.siblings( 'input[type=hidden]' ).remove();

		const $form = $( this );
		// Force a GET request when "Remember selection for future searches" isn't checked and
		// no user setting will be written to the database.
		const method = $form.find( '[name=nsRemember]' ).prop( 'checked' ) ? 'post' : 'get';
		$form.prop( 'method', method );

		forceFileTypeNamespaceWhenSearchForFileType( $searchField, state );
		const compiledQuery = ( $searchField.val() + ' ' + queryCompiler.compileSearchQuery( state ) ).trim(),
			$compiledSearchField = $( '<input>' ).prop( {
				name: 'search',
				type: 'hidden'
			} ).val( compiledQuery );
		$searchField.prop( 'name', '' )
			.after( $compiledSearchField );

		// Skip the default to avoid noise in the user's address bar
		if ( state.getSortMethod() !== 'relevance' ) {
			$searchField.after( $( '<input>' ).prop( {
				name: 'sort',
				type: 'hidden'
			} ).val( state.getSortMethod() ) );
		}
	} );
};

/**
 * @param {SearchModel} currentState
 */
const updateSearchResultLinks = function ( currentState ) {
	let extraParams = '';
	const sort = currentState.getSortMethod();
	const json = currentState.toJSON();

	// Skip the default to avoid noise in the user's address bar
	if ( sort !== 'relevance' ) {
		extraParams += '&sort=' + sort;
	}
	if ( json ) {
		extraParams += '&advancedSearch-current=' + json;
	}

	if ( extraParams ) {
		$( '.mw-prevlink, .mw-nextlink, .mw-numlink' ).attr( 'href', ( i, href ) => href + extraParams );
	}
};

/**
 * @return {FieldCollection}
 */
const createFieldConfiguration = function () {
	const fields = new FieldCollection();
	addDefaultFields( fields );
	fields.freezeGroups( [ 'text', 'structure', 'files' ] );

	/**
	 * Fired after the default fields have been added to the {@see FieldCollection}. Hook handlers
	 * can add additional fields and possibly modify existing ones. See docs/adding_fields.md for an
	 * example.
	 *
	 * @event advancedSearch.configureFields
	 * @param {FieldCollection} fields
	 * @stable to use
	 */
	mw.hook( 'advancedSearch.configureFields' ).fire( fields );

	return fields;
};

/**
 * @param {SearchModel} state
 * @param {FieldCollection} fields
 * @param {FieldElementBuilder} advancedOptionsBuilder
 * @return {jQuery}
 */
const buildPaneElement = function ( state, fields, advancedOptionsBuilder ) {
	const searchPreview = new SearchPreview( state, {
		fieldNames: fields.getFieldIds()
	} );

	const pane = new ExpandablePane( {
		dependentPaneContentBuilder: function () {
			return advancedOptionsBuilder.buildAllFieldsElement( fields );
		},
		label: mw.msg( 'advancedsearch-options-pane-head' ),
		$buttonContent: searchPreview.$element,
		tabIndex: 0,
		suffix: 'options'
	} );
	pane.on( 'change', ( open ) => {
		searchPreview.togglePreview( !open );
	} );

	// Proactively lazy-load the pane: if the user hasn't already clicked to open the pane,
	// build it in the background.
	mw.requestIdleCallback( () => {
		mw.loader.using( 'ext.advancedSearch.SearchFieldUI' ).then( () => {
			pane.buildDependentPane();
		} );
	} );

	return pane.$element;
};

/**
 * @param {SearchModel} state
 * @param {jQuery} header
 * @param {NamespacePresets} presets
 * @param {NamespaceFilters} selection
 * @param {Object.<int,string>} searchableNamespaces Mapping namespace ids to localized names
 * @return {jQuery}
 */
const buildNamespacesPaneElement = function ( state, header, presets, selection, searchableNamespaces ) {
	const nsPreview = new NamespacesPreview( state, {
		namespacesLabels: searchableNamespaces
	} );
	const $container = $( '<div>' ).addClass( 'mw-advancedSearch-namespace-selection' );
	const pane = new ExpandablePane( {
		dependentPaneContentBuilder: function () {
			return $container.append( header ).append( presets.$element ).append( selection.$element );
		},
		label: mw.msg( 'advancedsearch-namespaces-search-in' ),
		$buttonContent: nsPreview.$element,
		tabIndex: 0,
		suffix: 'namespaces'
	} );
	pane.on( 'change', ( open ) => {
		nsPreview.togglePreview( !open );
	} );
	pane.buildDependentPane();
	return pane.$element;
};

/**
 * @param {Object.<int,string>} searchableNamespaces Mapping namespace ids to localized names
 * @return {string[]}
 */
const getNamespacesFromUrl = function ( searchableNamespaces ) {
	const nsParamRegExp = /[?&]ns(\d+)\b/g;
	const namespaces = [];
	let nsMatch;
	while ( ( nsMatch = nsParamRegExp.exec( location.href ) ) &&
		nsMatch[ 1 ] in searchableNamespaces
	) {
		namespaces.push( nsMatch[ 1 ] );
	}
	return namespaces;
};

/**
 * @param {SearchField[]} fields
 * @return {Object} fieldId => default value pairs
 */
const getDefaultsFromConfig = function ( fields ) {
	return fields.reduce( ( defaults, field ) => {
		defaults[ field.id ] = field.defaultValue;
		return defaults;
	}, {} );
};

/**
 * @param {Object.<int,string>} searchableNamespaces Mapping namespace ids to localized names
 * @param {FieldCollection} fieldCollection
 * @return {SearchModel}
 */
const initState = function ( searchableNamespaces, fieldCollection ) {
	const state = new SearchModel(
			getDefaultNamespaces( mw.user.options.values ),
			getDefaultsFromConfig( fieldCollection.fields )
		),
		namespacesFromUrl = getNamespacesFromUrl( searchableNamespaces ),
		stateFromUrl = mw.util.getParamValue( 'advancedSearch-current' ),
		sortMethodFromUrl = mw.util.getParamValue( 'sort' );

	if ( namespacesFromUrl.length ) {
		state.setNamespaces( namespacesFromUrl );
	}
	if ( sortMethodFromUrl ) {
		state.setSortMethod( sortMethodFromUrl );
	}

	// If AdvancedSearch has occurred before, it's fields have the highest precedence
	if ( stateFromUrl ) {
		state.setAllFromJSON( stateFromUrl );
	}

	return state;
};

$( () => {
	const searchableNamespaces = mw.config.get( 'advancedSearch.searchableNamespaces' ),
		fieldCollection = createFieldConfiguration(),
		state = initState( searchableNamespaces, fieldCollection ),
		advancedOptionsBuilder = new FieldElementBuilder( state ),
		queryCompiler = new QueryCompiler( fieldCollection.fields );

	const $search = $( 'form#search, form#powersearch' ),
		$advancedSearch = $( '<div>' ).addClass( 'mw-advancedSearch-container' ),
		$searchField = $search.find( 'input[name="search"]' ),
		$profileField = $search.find( 'input[name="profile"]' );

	// There is possibly no form, e.g. when the special page failed (T266163)
	if ( !$searchField.length ) {
		return;
	}

	$search.append( $advancedSearch );

	const term = $searchField.val(),
		autoFocus = !term.trim();

	$searchField.val( queryCompiler.removeCompiledQueryFromSearch( term, state ) );

	// Autofocus is handled by mediawiki on simple search, but AdvancedSearch breaks it.
	// Search field need to be focused only when search term is empty.
	if ( autoFocus ) {
		$searchField.trigger( 'focus' );
	}

	$profileField.val( 'advanced' );

	setSearchSubmitTrigger( $search, $searchField, state, queryCompiler );

	$advancedSearch.append( buildPaneElement( state, fieldCollection, advancedOptionsBuilder ) );

	updateSearchResultLinks( state );

	const currentSearch = new FormState( state, {
		name: 'advancedSearch-current'
	} );

	$advancedSearch.append( currentSearch.$element );
	const namespaceSelection = new NamespaceFilters( state, {
			namespaces: searchableNamespaces,
			placeholder: mw.msg( 'advancedsearch-namespaces-placeholder' ),
			$overlay: true
		} ),
		namespacePresets = new NamespacePresets(
			state,
			new NamespacePresetProviders( searchableNamespaces ),
			{
				presets: mw.config.get( 'advancedSearch.namespacePresets' )
			}
		),
		$headerContainer = $( '<div>' ).addClass( 'mw-advancedSearch-namespace-selection-header' );

	if ( mw.user.isNamed() ) {
		const rememberNameSpaceSelection = new OO.ui.FieldLayout( new OO.ui.CheckboxInputWidget( {
			value: mw.user.tokens.get( 'searchnamespaceToken' ),
			name: 'nsRemember'
		} ), { label: mw.msg( 'advancedsearch-namespaces-remember' ), align: 'inline' } );
		$headerContainer.append( rememberNameSpaceSelection.$element );
	}

	$advancedSearch.append( buildNamespacesPaneElement(
		state,
		$headerContainer,
		namespacePresets,
		namespaceSelection,
		searchableNamespaces
	) );

	// remove old namespace selection item to avoid double ns parameters
	$( '.mw-search-spinner, #mw-searchoptions' ).remove();

	// TODO this is workaround to fix a toggle true event fired after the DOM is loaded
	setTimeout( () => {
		namespaceSelection.getMenu().toggle( false );
	}, 0 );
} );
