/**
 * @license GPL-2.0-or-later
 * @author Adrian Heine <adrian.heine@wikimedia.de>
 */
( function ( wb ) {
	'use strict';

	var MODULE = wb;
	var PARENT = util.ContentLanguages;
	var monolingualTextLanguages = require( './contentLanguages.json' ).monolingualtext;
	var termLanguages = require( './contentLanguages.json' ).term;

	/**
	 * @constructor
	 */
	var SELF = MODULE.WikibaseContentLanguages = util.inherit(
		'WbContentLanguages',
		PARENT,
		function ( contentLanguages ) {
			if ( !Array.isArray( contentLanguages ) ) {
				throw new Error( 'Required parameter "contentLanguages" is not specified properly.' );
			}

			this._languageCodes = contentLanguages;
			this._languageNameMap = mw.config.get( 'wgULSLanguages' );
		}
	);

	SELF.getMonolingualTextLanguages = function () {
		return new SELF( monolingualTextLanguages );
	};

	SELF.getTermLanguages = function () {
		return new SELF( termLanguages );
	};

	$.extend( SELF.prototype, {
		/**
		 * @type {Object|null}
		 * @private
		 */
		_languageNameMap: null,

		/**
		 * @type {string[]|null}
		 * @private
		 */
		_languageCodes: null,

		/**
		 * @inheritdoc
		 */
		getAll: function () {
			return this._languageCodes;
		},

		/**
		 * @inheritdoc
		 */
		getName: function ( code ) {
			return this._languageNameMap ? this._languageNameMap[ code ] : null;
		},

		getLanguageNameMap: function () {
			var map = {},
				self = this;

			this._languageCodes.forEach( function ( languageCode ) {
				map[ languageCode ] = self.getName( languageCode );
			} );

			return map;
		}
	} );

}( wikibase ) );
