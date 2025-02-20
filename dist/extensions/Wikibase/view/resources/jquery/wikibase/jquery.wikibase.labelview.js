( function ( wb ) {
	'use strict';

	var PARENT = $.ui.EditableTemplatedWidget,
		datamodel = require( 'wikibase.datamodel' );

	/**
	 * Displays and allows editing of a `datamodel.Term` acting as an `Entity`'s label.
	 *
	 * @class jQuery.wikibase.labelview
	 * @extends jQuery.ui.EditableTemplatedWidget
	 * @license GPL-2.0-or-later
	 * @author H. Snater < mediawiki@snater.com >
	 *
	 * @constructor
	 *
	 * @param {Object} options
	 * @param {datamodel.Term} options.value
	 * @param {string} [options.helpMessage=mw.msg( 'wikibase-label-input-help-message' )]
	 */
	$.widget( 'wikibase.labelview', PARENT, {
		/**
		 * @inheritdoc
		 * @protected
		 */
		options: {
			template: 'wikibase-labelview',
			templateParams: [
				'', // additional class
				'', // text
				'', // toolbar
				'auto', // dir
				'' // lang
			],
			templateShortCuts: {
				$text: '.wikibase-labelview-text'
			},
			value: null,
			allLanguages: function () {
				return {};
			},
			inputNodeName: 'TEXTAREA',
			helpMessage: mw.msg( 'wikibase-label-input-help-message' ),
			showEntityId: false
		},

		/**
		 * @inheritdoc
		 * @protected
		 *
		 * @throws {Error} if a required option is not specified properly.
		 */
		_create: function () {
			if ( !( this.options.value instanceof datamodel.Term )
				|| this.options.inputNodeName !== 'INPUT' && this.options.inputNodeName !== 'TEXTAREA'
			) {
				throw new Error( 'Required option not specified properly' );
			}

			var self = this;

			this.element
			.on(
				'labelviewafterstartediting.' + this.widgetName
				+ ' eachchange.' + this.widgetName,
				function ( event ) {
					if ( self.value().getText() === '' ) {
						// Since the widget shall not be in view mode when there is no value, triggering
						// the event without a proper value is only done when creating the widget. Disabling
						// other edit buttons shall be avoided.
						// TODO: Move logic to a sensible place.
						self.element.addClass( 'wb-empty' );
						return;
					}

					self.element.removeClass( 'wb-empty' );
				}
			);

			PARENT.prototype._create.call( this );

			if ( this.$text.text() === '' ) {
				this.draw();
			}
		},

		/**
		 * @inheritdoc
		 */
		destroy: function () {
			if ( this.isInEditMode() ) {
				var self = this;

				this.element.one( this.widgetEventPrefix + 'afterstopediting', function ( event ) {
					PARENT.prototype.destroy.call( self );
				} );

				this.stopEditing( true );
			} else {
				PARENT.prototype.destroy.call( this );
			}
		},

		_getPlaceholderLabel: function ( languageCode ) {
			if ( !wb.fallbackChains || wb.fallbackChains.length !== undefined ) {
				return null;
			}
			var chain = wb.fallbackChains[ languageCode ];

			if ( !chain ) {
				// If language is unknown, e.g. ?uselang=qqx,
				return null;
			}

			if ( chain[ chain.length - 1 ] === 'en' ) {
				// Remove implicit fallback to English. This only works if `mul` is enabled and in the chain.
				chain = chain.slice( 0, -1 );
			}

			// TODO: should be a for-of loop as soon as we can use #ES6
			for ( var i = 0; i < chain.length; i++ ) {
				var langCode = chain[ i ];
				if ( this.options.allLanguages().hasItemForKey( langCode ) ) {
					return this.options.allLanguages().getItemByKey( langCode );
				}
			}
			return null;
		},

		_getLanguageAwareInputPlaceholder: function ( languageCode ) {
			if ( languageCode === 'mul' ) {
				return mw.msg( 'wikibase-label-edit-placeholder-mul' );
			}
			return mw.msg(
				'wikibase-label-edit-placeholder-language-aware',
				wb.getLanguageNameByCodeForTerms( languageCode )
			);
		},

		/**
		 * @inheritdoc
		 */
		draw: function () {
			var self = this,
				deferred = $.Deferred(),
				languageCode = this.options.value.getLanguageCode(),
				labelText = this.options.value.getText();

			if ( labelText === '' ) {
				labelText = null;
			}

			this.element.toggleClass( 'wb-empty', !labelText );

			if ( !this.isInEditMode() && !labelText ) {
				var placeholderTerm = self._getPlaceholderLabel( languageCode );
				var text;
				var textLanguage;
				if ( placeholderTerm === null ) {
					text = mw.msg( 'wikibase-label-empty' );
					textLanguage = mw.config.get( 'wgUserLanguage' );
				} else {
					text = placeholderTerm.getText();
					textLanguage = placeholderTerm.getLanguageCode();
				}
				this.$text.text( text );
				this.element
					.attr( 'lang', textLanguage )
					.attr( 'dir', $.util.getDirectionality( textLanguage ) );
				return deferred.resolve().promise();
			}

			this.element
			.attr( 'lang', languageCode )
			.attr( 'dir', $.util.getDirectionality( languageCode ) );

			if ( !this.isInEditMode() ) {
				this.$text.text( labelText );
				return deferred.resolve().promise();
			}

			var $input = $( document.createElement( this.options.inputNodeName ) );
			var inputPlaceholder = this._getLanguageAwareInputPlaceholder( languageCode );
			if ( !labelText && this._getPlaceholderLabel( languageCode ) !== null ) {
				inputPlaceholder = this._getPlaceholderLabel( languageCode ).getText();
			}

			$input
			.addClass( this.widgetFullName + '-input' )
			// TODO: Inject correct placeholder via options
			.attr( 'placeholder', inputPlaceholder )
			.attr( 'lang', languageCode )
			.attr( 'dir', $.util.getDirectionality( languageCode ) )
			.on( 'keydown.' + this.widgetName, function ( event ) {
				if ( event.keyCode === $.ui.keyCode.ENTER ) {
					event.preventDefault();
				}
			} )
			.on( 'eachchange.' + this.widgetName, function ( event ) {
				self._trigger( 'change' );
			} );

			if ( labelText ) {
				$input.val( labelText );
			}

			if ( $.fn.inputautoexpand ) {
				$input.inputautoexpand( {
					expandHeight: true,
					suppressNewLine: true
				} );
			}

			this.$text.empty().append( $input );

			return deferred.resolve().promise();
		},

		_startEditing: function () {
			// FIXME: This could be much faster
			return this.draw();
		},

		_stopEditing: function ( dropValue ) {
			if ( dropValue && this.options.value.getText() === '' ) {
				this.$text.children( '.' + this.widgetFullName + '-input' ).val( '' );
			}
			// FIXME: This could be much faster
			return this.draw();
		},

		/**
		 * @inheritdoc
		 * @protected
		 *
		 * @throws {Error} when trying to set the widget's value to something other than a
		 *         `datamodel.Term` instance.
		 */
		_setOption: function ( key, value ) {
			if ( key === 'value' && !( value instanceof datamodel.Term ) ) {
				throw new Error( 'Value needs to be a datamodel.Term instance' );
			}

			var response = PARENT.prototype._setOption.call( this, key, value );

			if ( key === 'disabled' && this.isInEditMode() ) {
				this.$text.children( '.' + this.widgetFullName + '-input' ).prop( 'disabled', value );
			}

			return response;
		},

		/**
		 * @inheritdoc
		 *
		 * @param {datamodel.Term} [value]
		 * @return {datamodel.Term|undefined}
		 */
		value: function ( value ) {
			if ( value !== undefined ) {
				return this.option( 'value', value );
			}

			if ( !this.isInEditMode() ) {
				return this.options.value;
			}

			return new datamodel.Term(
				this.options.value.getLanguageCode(),
				this.$text.children( '.' + this.widgetFullName + '-input' ).val().trim()
			);
		},

		/**
		 * @inheritdoc
		 */
		focus: function () {
			if ( this.isInEditMode() ) {
				this.$text.children( '.' + this.widgetFullName + '-input' ).trigger( 'focus' );
			} else {
				this.element.trigger( 'focus' );
			}
		}

	} );

}( wikibase ) );
