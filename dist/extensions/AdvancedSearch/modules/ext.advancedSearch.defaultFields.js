'use strict';

const {
	ArbitraryWordInput,
	DeepCategoryFilter,
	FileTypeOptionProvider,
	FileTypeSelection,
	ImageDimensionInput,
	ImageDimensionLayout,
	LanguageOptionProvider,
	LanguageSelection,
	SortPreference,
	TemplateSearch,
	TextInput
} = require( 'ext.advancedSearch.SearchFieldUI' );
const { createSearchFieldFromObject } = require( './ext.advancedSearch.SearchField.js' );

/**
 * @param {string} val
 * @return {string}
 */
const trimQuotes = function ( val ) {
	val = val.replace( /^"((?:\\.|[^"\\])+)"$/, '$1' );
	if ( !/^"/.test( val ) ) {
		val = val.replace( /\\(.)/g, '$1' );
	}
	return val;
};

/**
 * @param {string} val
 * @return {string}
 */
const enforceQuotes = function ( val ) {
	return '"' + trimQuotes( val ).replace( /(["\\])/g, '\\$1' ) + '"';
};

/**
 * @param {string} val
 * @return {string}
 */
const optionalQuotes = function ( val ) {
	return /\s/.test( val ) ? enforceQuotes( val ) : trimQuotes( val );
};

/**
 * @param {string} prefix
 * @param {string[]} val
 * @return {string}
 */
const formatSizeConstraint = function ( prefix, val ) {
	if ( !Array.isArray( val ) || val.length < 2 || ( val[ 1 ] || '' ).trim() === '' ) {
		return '';
	}
	return prefix + val.join( '' );
};

/**
 * @param {string} id
 * @return {string|false}
 */
const getOptionHelpMessage = function ( id ) {
	// The following messages are used here:
	// * advancedsearch-help-deepcategory
	// * advancedsearch-help-fileh
	// * advancedsearch-help-filetype
	// * advancedsearch-help-filew
	// * advancedsearch-help-hastemplate
	// * advancedsearch-help-inlanguage
	// * advancedsearch-help-intitle
	// * advancedsearch-help-not
	// * advancedsearch-help-or
	// * advancedsearch-help-phrase
	// * advancedsearch-help-plain
	// * advancedsearch-help-sort
	// * advancedsearch-help-subpageof
	const message = mw.config.get( 'advancedSearch.tooltips' )[ 'advancedsearch-help-' + id ];
	if ( !message ) {
		return false;
	}

	// The following messages are used here:
	// * advancedsearch-field-deepcategory
	// * advancedsearch-field-fileh
	// * advancedsearch-field-filetype
	// * advancedsearch-field-filew
	// * advancedsearch-field-hastemplate
	// * advancedsearch-field-inlanguage
	// * advancedsearch-field-intitle
	// * advancedsearch-field-not
	// * advancedsearch-field-or
	// * advancedsearch-field-phrase
	// * advancedsearch-field-plain
	// * advancedsearch-field-sort
	// * advancedsearch-field-subpageof
	const head = mw.msg( 'advancedsearch-field-' + id );
	return new OO.ui.HtmlSnippet( '<h6 class="mw-advancedSearch-tooltip-head">' + head + '</h6>' +
		'<div class="mw-advancedSearch-tooltip-body">' + message + '</div>' );
};

/**
 * @param {OO.ui.Widget} widget
 * @param {string} id
 * @return {OO.ui.FieldLayout}
 */
const createDefaultLayout = function ( widget, id ) {
	const $fieldLayout = new OO.ui.FieldLayout(
		widget,
		{
			// Messages documented in getOptionHelpMessage
			label: mw.msg( 'advancedsearch-field-' + id ),
			align: 'right',
			help: getOptionHelpMessage( id ),
			$overlay: true
		}
	);

	$fieldLayout.$help.find( 'a' )
		.attr( 'aria-description',
			mw.msg( 'advancedsearch-help-general-instruction',
				mw.msg( 'advancedsearch-field-' + id ) )
		);

	return $fieldLayout;
};

/**
 * Create a layout widget that can react to state changes
 *
 * @param {OO.ui.Widget} widget Widget to wrap in a OO.ui.FieldLayout
 * @param {string} id
 * @param {SearchModel} state
 * @return {ImageDimensionLayout}
 */
const createImageDimensionLayout = function ( widget, id, state ) {
	return new ImageDimensionLayout(
		state,
		widget,
		{
			// Messages documented in getOptionHelpMessage
			label: mw.msg( 'advancedsearch-field-' + id ),
			align: 'right',
			checkVisibility: function () {
				return state.filetypeSupportsDimensions();
			},
			help: getOptionHelpMessage( id ),
			$overlay: true
		}
	);
};

/**
 * @param {FieldCollection} fieldCollection
 */
const addDefaultFields = function ( fieldCollection ) {
	// Text Group
	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'plain',
			defaultValue: [],
			formatter: function ( val ) {
				if ( Array.isArray( val ) ) {
					return val.join( ' ' );
				}
				return val;
			},
			init: function ( state, config ) {
				return new ArbitraryWordInput( state, config );
			},
			layout: createDefaultLayout
		} ),
		'text'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'phrase',
			formatter: function ( val ) {
				return val;
			},
			init: function ( state, config ) {
				return new TextInput(
					state,
					Object.assign( {}, config, { placeholder: mw.msg( 'advancedsearch-placeholder-exact-text' ) } )
				);
			},
			layout: createDefaultLayout
		} ),
		'text'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'not',
			defaultValue: [],
			formatter: function ( val ) {
				if ( Array.isArray( val ) ) {
					return val.map( ( el ) => '-' + el ).join( ' ' );
				}
				return '-' + val;
			},
			init: function ( state, config ) {
				return new ArbitraryWordInput( state, config );
			},
			layout: createDefaultLayout
		} ),
		'text'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'or',
			defaultValue: [],
			formatter: function ( val ) {
				if ( Array.isArray( val ) ) {
					return val.map( optionalQuotes ).join( ' OR ' );
				}
				return optionalQuotes( val );
			},
			init: function ( state, config ) {
				return new ArbitraryWordInput( state, config );
			},
			layout: createDefaultLayout
		} ),
		'text'
	);

	// Structure Group
	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'intitle',
			formatter: function ( val ) {
				return 'intitle:' + optionalQuotes( val );
			},
			init: function ( state, config ) {
				return new TextInput( state, config );
			},
			layout: createDefaultLayout
		} ),
		'structure'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'subpageof',
			formatter: function ( val ) {
				return 'subpageof:' + optionalQuotes( val );
			},
			init: function ( state, config ) {
				return new TextInput( state, config );
			},
			layout: createDefaultLayout
		} ),
		'structure'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'deepcategory',
			formatter: function ( val ) {
				const keyword = mw.config.get( 'advancedSearch.deepcategoryEnabled' ) ? 'deepcat:' : 'incategory:';
				if ( Array.isArray( val ) ) {
					return val.map( ( templateItem ) => keyword + optionalQuotes( templateItem ) ).join( ' ' );
				}
				return keyword + optionalQuotes( val );
			},
			init: function ( state, config ) {
				return new DeepCategoryFilter(
					state,
					Object.assign( {}, config, { lookupId: 'category' } )
				);
			},
			layout: createDefaultLayout
		} ),
		'structure'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'hastemplate',
			defaultValue: [],
			formatter: function ( val ) {
				if ( Array.isArray( val ) ) {
					return val.map( ( templateItem ) => 'hastemplate:' + optionalQuotes( templateItem ) ).join( ' ' );
				}
				return 'hastemplate:' + optionalQuotes( val );
			},
			init: function ( state, config ) {
				return new TemplateSearch(
					state,
					Object.assign( {}, config, { lookupId: 'template' } )
				);
			},
			customEventHandling: true,
			layout: createDefaultLayout
		} ),
		'structure'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'inlanguage',
			formatter: function ( val ) {
				return 'inlanguage:' + val;
			},
			init: function ( state, config ) {
				return new LanguageSelection(
					state,
					new LanguageOptionProvider( mw.config.get( 'advancedSearch.languages' ) ),
					Object.assign( {}, config, { dropdown: { $overlay: true } } )
				);
			},
			enabled: function () {
				return mw.config.get( 'advancedSearch.languages' ) !== null;
			},
			layout: createDefaultLayout
		} ),
		'structure'
	);

	// Files Group
	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'filetype',
			formatter: function ( val ) {
				switch ( val ) {
					case 'bitmap':
					case 'audio':
					case 'drawing':
					case 'office':
					case 'video':
						return 'filetype:' + val;
					default:
						return 'filemime:' + val;
				}
			},
			init: function ( state, config ) {
				return new FileTypeSelection(
					state,
					new FileTypeOptionProvider( mw.config.get( 'advancedSearch.mimeTypes' ) ),
					Object.assign( {}, config, { dropdown: { $overlay: true } } )
				);
			},
			layout: createDefaultLayout
		} ),
		'files'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'filew',
			defaultValue: [ '>', '' ],
			formatter: function ( val ) {
				return formatSizeConstraint( 'filew:', val );
			},
			init: function ( state, config ) {
				return new ImageDimensionInput( state, config );
			},
			layout: createImageDimensionLayout
		} ),
		'files'
	);

	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'fileh',
			defaultValue: [ '>', '' ],
			formatter: function ( val ) {
				return formatSizeConstraint( 'fileh:', val );
			},
			init: function ( state, config ) {
				return new ImageDimensionInput( state, config );
			},
			layout: createImageDimensionLayout
		} ),
		'files'
	);

	// Sorting options
	fieldCollection.add(
		createSearchFieldFromObject( {
			id: 'sort',
			defaultValue: 'relevance',
			formatter: function () {
				// Doesn't become a keyword in …&search=…, but it's own …&sort=… parameter
				return '';
			},
			init: function ( state, config ) {
				return new SortPreference( state, config );
			},
			customEventHandling: true,
			layout: createDefaultLayout
		} ),
		'sort'
	);
};

module.exports = addDefaultFields;
