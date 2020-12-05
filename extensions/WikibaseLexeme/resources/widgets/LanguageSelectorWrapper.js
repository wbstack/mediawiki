module.exports = ( function ( mw, wikibase ) {
	'use strict';

	return function ( contentLanguages ) {
		return {
			props: [ 'initialCode' ],
			template: '<input>',
			mounted: function () {
				var vm = this,
					languageMenuOptions,
					$input = $( this.$el ),
					initialLabel = '';

				var getLanguageLabel = function ( code ) {
					var languageName = wikibase.getLanguageNameByCode( code );
					if ( languageName === code ) {
						return code;
					}
					return mw.message(
						'wikibase-lexeme-language-selector-label',
						languageName,
						code
					).text();
				};

				var languages = contentLanguages.getAll();

				if ( languages !== null ) {

					var _labels = {};
					languages.forEach( function ( code ) {
						_labels[ code ] = getLanguageLabel( code );
					} );
				}

				languageMenuOptions = Object.keys( _labels ).map( function ( code ) {
					return { code: code, label: _labels[ code ] };
				} );

				$input.languagesuggester( {
					source: languageMenuOptions
				} );

				if ( this.initialCode ) {
					initialLabel = getLanguageLabel( this.initialCode );
					$input.data( 'languagesuggester' ).setSelectedValue(
						this.initialCode,
						initialLabel
					);
				}

				$input.on( 'languagesuggesterchange', function ( /* e */ ) {
					var languageSuggester = $( vm.$el ).data( 'languagesuggester' ),
						selectedMenuValue = languageSuggester && languageSuggester.getSelectedValue(),
						value = selectedMenuValue || $input.val();

					vm.$emit( 'input', value );
				} );

			},
			watch: {
				value: function ( value ) {
					$( this.$el ).data( 'languagesuggester' ).setSelectedValue( value, value );
				}
			},
			destroyed: function () {
				$( this.$el ).data( 'languagesuggester' ).destroy();
			},
			computed: {

			}
		};
	};

} )( mw, wikibase );
