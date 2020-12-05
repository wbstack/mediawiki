( function () {
	'use strict';

	mw.libs = mw.libs || {};
	mw.libs.advancedSearch = mw.libs.advancedSearch || {};
	mw.libs.advancedSearch.dm = mw.libs.advancedSearch.dm || {};

	/**
	 * @param {string} val
	 * @return {string}
	 */
	function trimQuotes( val ) {
		val = val.replace( /^"((?:\\.|[^"\\])+)"$/, '$1' );
		if ( !/^"/.test( val ) ) {
			val = val.replace( /\\(.)/g, '$1' );
		}
		return val;
	}

	/**
	 * @param {string} val
	 * @return {string}
	 */
	function enforceQuotes( val ) {
		return '"' + trimQuotes( val ).replace( /(["\\])/g, '\\$1' ) + '"';
	}

	/**
	 * @param {string} val
	 * @return {string}
	 */
	function optionalQuotes( val ) {
		return /\s/.test( val ) ? enforceQuotes( val ) : trimQuotes( val );
	}

	/**
	 * @param {string} prefix
	 * @param {string[]} val
	 * @return {string}
	 */
	function formatSizeConstraint( prefix, val ) {
		if ( !Array.isArray( val ) || val.length < 2 || ( val[ 1 ] || '' ).trim() === '' ) {
			return '';
		}
		return prefix + val.join( '' );
	}

	/**
	 * @param {string} messageKey
	 * @return {string}
	 */
	function getMessage( messageKey ) {
		// use prepared tooltip because of mw.message deficiencies
		var tooltips = mw.config.get( 'advancedSearch.tooltips' );
		return tooltips[ messageKey ] || '';
	}

	/**
	 * @param {Object} option
	 * @return {string|boolean}
	 */
	function getOptionHelpMessage( option ) {
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
		var message = getMessage( 'advancedsearch-help-' + option.id );
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
		var head = mw.msg( 'advancedsearch-field-' + option.id );
		if ( !message || !head ) {
			return false;
		}

		return new OO.ui.HtmlSnippet( '<h6 class="mw-advancedSearch-tooltip-head">' + head + '</h6>' + message );
	}

	/**
	 * @param {OO.ui.Widget} widget
	 * @param {Object} option
	 * @return {OO.ui.FieldLayout}
	 */
	function createDefaultLayout( widget, option ) {
		return new OO.ui.FieldLayout(
			widget,
			{
				// Messages documented in getOptionHelpMessage
				label: mw.msg( 'advancedsearch-field-' + option.id ),
				align: 'right',
				help: getOptionHelpMessage( option ),
				$overlay: true
			}
		);
	}

	/**
	 * Create a layout widget that can react to state changes
	 *
	 * @param {OO.ui.Widget} widget Widget to wrap in a OO.ui.FieldLayout
	 * @param {Object} option Options for OO.ui.FieldLayout
	 * @param {mw.libs.advancedSearch.dm.SearchModel} state
	 * @return {mw.libs.advancedSearch.ui.ImageDimensionLayout}
	 */
	function createImageDimensionLayout( widget, option, state ) {
		return new mw.libs.advancedSearch.ui.ImageDimensionLayout(
			state,
			widget,
			{
				// Messages documented in getOptionHelpMessage
				label: mw.msg( 'advancedsearch-field-' + option.id ),
				align: 'right',
				checkVisibility: function () {
					return state.filetypeSupportsDimensions();
				},
				help: getOptionHelpMessage( option ),
				$overlay: true
			}
		);
	}

	/**
	 * @param {mw.libs.advancedSearch.FieldCollection} fieldCollection
	 */
	mw.libs.advancedSearch.addDefaultFields = function ( fieldCollection ) {
		var createField = mw.libs.advancedSearch.createSearchFieldFromObject;

		// Text Group
		fieldCollection.add(
			createField( {
				id: 'plain',
				defaultValue: [],
				formatter: function ( val ) {
					if ( Array.isArray( val ) ) {
						return val.join( ' ' );
					}
					return val;
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.ArbitraryWordInput( state, config );
				},
				layout: createDefaultLayout
			} ),
			'text'
		);

		fieldCollection.add(
			createField( {
				id: 'phrase',
				defaultValue: '',
				formatter: function ( val ) {
					return val;
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.TextInput(
						state,
						$.extend( {}, config, { placeholder: mw.msg( 'advancedsearch-placeholder-exact-text' ) } )
					);
				},
				layout: createDefaultLayout
			} ),
			'text'
		);

		fieldCollection.add(
			createField( {
				id: 'not',
				defaultValue: [],
				formatter: function ( val ) {
					if ( Array.isArray( val ) ) {
						return val.map( function ( el ) {
							return '-' + el;
						} ).join( ' ' );
					}
					return '-' + val;
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.ArbitraryWordInput( state, config );
				},
				layout: createDefaultLayout
			} ),
			'text'
		);

		fieldCollection.add(
			createField( {
				id: 'or',
				defaultValue: [],
				formatter: function ( val ) {
					if ( Array.isArray( val ) ) {
						return val.map( optionalQuotes ).join( ' OR ' );
					}
					return optionalQuotes( val );
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.ArbitraryWordInput( state, config );
				},
				layout: createDefaultLayout
			} ),
			'text'
		);

		// Structure Group
		fieldCollection.add(
			createField( {
				id: 'intitle',
				defaultValue: '',
				formatter: function ( val ) {
					return 'intitle:' + optionalQuotes( val );
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.TextInput( state, config );
				},
				layout: createDefaultLayout
			} ),
			'structure'
		);

		fieldCollection.add(
			createField( {
				id: 'subpageof',
				defaultValue: '',
				formatter: function ( val ) {
					return 'subpageof:' + optionalQuotes( val );
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.TextInput( state, config );
				},
				layout: createDefaultLayout
			} ),
			'structure'
		);

		fieldCollection.add(
			createField( {
				id: 'deepcategory',
				formatter: function ( val ) {
					var keyword = mw.config.get( 'advancedSearch.deepcategoryEnabled' ) ? 'deepcat:' : 'incategory:';
					if ( Array.isArray( val ) ) {
						return val.map( function ( templateItem ) {
							return keyword + optionalQuotes( templateItem );
						} ).join( ' ' );
					}
					return keyword + optionalQuotes( val );
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.DeepCategoryFilter(
						state,
						$.extend( {}, config, { lookupId: 'category' } )
					);
				},
				layout: createDefaultLayout
			} ),
			'structure'
		);

		fieldCollection.add(
			createField( {
				id: 'hastemplate',
				defaultValue: [],
				formatter: function ( val ) {
					if ( Array.isArray( val ) ) {
						return val.map( function ( templateItem ) {
							return 'hastemplate:' + optionalQuotes( templateItem );
						} ).join( ' ' );
					}
					return 'hastemplate:' + optionalQuotes( val );
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.TemplateSearch(
						state,
						$.extend( {}, config, { lookupId: 'template' } )
					);
				},
				customEventHandling: true,
				layout: createDefaultLayout
			} ),
			'structure'
		);

		fieldCollection.add(
			createField( {
				id: 'inlanguage',
				defaultValue: '',
				formatter: function ( val ) {
					return 'inlanguage:' + val;
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.LanguageSelection(
						state,
						new mw.libs.advancedSearch.dm.LanguageOptionProvider( mw.config.get( 'advancedSearch.languages' ) ),
						$.extend( {}, config, { dropdown: { $overlay: true } } )
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
			createField( {
				id: 'filetype',
				defaultValue: '',
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
					return new mw.libs.advancedSearch.ui.FileTypeSelection(
						state,
						new mw.libs.advancedSearch.dm.FileTypeOptionProvider( mw.config.get( 'advancedSearch.mimeTypes' ) ),
						$.extend( {}, config, { dropdown: { $overlay: true } } )
					);
				},
				layout: createDefaultLayout
			} ),
			'files'
		);

		fieldCollection.add(
			createField( {
				id: 'filew',
				defaultValue: [ '>', '' ],
				formatter: function ( val ) {
					return formatSizeConstraint( 'filew:', val );
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.ImageDimensionInput( state, config );
				},
				layout: createImageDimensionLayout
			} ),
			'files'
		);

		fieldCollection.add(
			createField( {
				id: 'fileh',
				defaultValue: [ '>', '' ],
				formatter: function ( val ) {
					return formatSizeConstraint( 'fileh:', val );
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.ImageDimensionInput( state, config );
				},
				layout: createImageDimensionLayout
			} ),
			'files'
		);

		// Sorting options
		fieldCollection.add(
			createField( {
				id: 'sort',
				defaultValue: 'relevance',
				formatter: function () {
					// Does not return a keyword
					return '';
				},
				init: function ( state, config ) {
					return new mw.libs.advancedSearch.ui.SortPreference( state, config );
				},
				customEventHandling: true,
				layout: createDefaultLayout
			} ),
			'sort'
		);
	};

	// Ideas for Version 2.0:
	// * Meta ( linksto:, neartitle:, morelike: )
}() );
