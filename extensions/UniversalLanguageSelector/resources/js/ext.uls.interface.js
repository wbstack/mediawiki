/*!
 * ULS interface integration logic
 *
 * Copyright (C) 2012-2013 Alolita Sharma, Amir Aharoni, Arun Ganesh, Brandon Harris,
 * Niklas Laxström, Pau Giner, Santhosh Thottingal, Siebrand Mazeland and other
 * contributors. See CREDITS for a list.
 *
 * UniversalLanguageSelector is dual licensed GPLv2 or later and MIT. You don't
 * have to do anything special to choose one license or the other and you don't
 * have to notify anyone which license you are using. You are free to use
 * UniversalLanguageSelector in commercial projects as long as the copyright
 * header is left intact. See files GPL-LICENSE and MIT-LICENSE for details.
 *
 * @file
 * @ingroup Extensions
 * @licence GNU General Public Licence 2.0 or later
 * @licence MIT License
 */

( function () {
	'use strict';
	var languageSettingsModules = [ 'ext.uls.displaysettings' ],
		launchULS = require( './ext.uls.launch.js' );

	/**
	 * Construct the display settings link
	 *
	 * @return {jQuery}
	 */
	function displaySettings() {
		return $( '<button>' )
			.addClass( 'display-settings-block' )
			.attr( {
				title: $.i18n( 'ext-uls-display-settings-desc' ),
				'data-i18n': 'ext-uls-display-settings-title'
			} )
			.i18n();
	}

	/**
	 * Construct the input settings link
	 *
	 * @return {jQuery}
	 */
	function inputSettings() {
		return $( '<button>' )
			.addClass( 'input-settings-block' )
			.attr( {
				title: $.i18n( 'ext-uls-input-settings-desc' ),
				'data-i18n': 'ext-uls-input-settings-title'
			} )
			.i18n();
	}

	/**
	 * For Vector: Check whether the classic Vector or "new" vector ([[mw:Desktop_improvements]]) is enabled based
	 * on the contents of the page.
	 * For other skins, check if ULSDisplayInputAndDisplaySettingsInInterlanguage contains the current skin.
	 *
	 * @return {bool}
	 */
	function isUsingStandaloneLanguageButton() {
		var skin = mw.config.get( 'skin' );
		// special handling for Vector. This can be removed when Vector is split into 2 separate skins.
		return skin === 'vector' ? $( '#p-lang-btn' ).length > 0 :
			mw.config.get( 'wgULSDisplaySettingsInInterlanguage' );
	}

	/**
	 * Add display settings link to the settings bar in ULS
	 *
	 * @param {Object} uls The ULS object
	 */
	function addDisplaySettings( uls ) {
		var $displaySettings = displaySettings();

		uls.$menu.find( '#uls-settings-block' ).append( $displaySettings );
		// Initialize the trigger
		$displaySettings.one( 'click', function () {
			$displaySettings.languagesettings( {
				defaultModule: 'display',
				onClose: uls.show.bind( uls ),
				onPosition: uls.position.bind( uls ),
				onVisible: uls.hide.bind( uls )
			} ).trigger( 'click' );
		} );
	}

	/**
	 * Add input settings link to the settings bar in ULS
	 *
	 * @param {Object} uls The ULS object
	 */
	function addInputSettings( uls ) {
		var $inputSettings = inputSettings();

		uls.$menu.find( '#uls-settings-block' ).append( $inputSettings );
		// Initialize the trigger
		$inputSettings.one( 'click', function () {
			$inputSettings.languagesettings( {
				defaultModule: 'input',
				onClose: uls.show.bind( uls ),
				onPosition: uls.position.bind( uls ),
				onVisible: uls.hide.bind( uls )
			} ).trigger( 'click' );
		} );
	}

	function userCanChangeLanguage() {
		return mw.config.get( 'wgULSAnonCanChangeLanguage' ) || !mw.user.isAnon();
	}

	/**
	 * The tooltip to be shown when language changed using ULS.
	 * It also allows to undo the language selection.
	 *
	 * @param {string} previousLang
	 * @param {string} previousAutonym
	 */
	function showUndoTooltip( previousLang, previousAutonym ) {
		var $ulsTrigger, ulsPopup, ulsPopupPosition,
			ulsPosition = mw.config.get( 'wgULSPosition' );

		$ulsTrigger = ( ulsPosition === 'interlanguage' ) ?
			$( '.uls-settings-trigger, .mw-interlanguage-selector' ) :
			$( '.uls-trigger' );

		function hideTipsy() {
			ulsPopup.toggle( false );
		}

		function showTipsy( timeout ) {
			var tipsyTimer = 0;

			ulsPopup.toggle( true );
			ulsPopup.toggleClipping( false );

			// if the mouse is over the tooltip, do not hide
			$( '.uls-tipsy' ).on( 'mouseover', function () {
				clearTimeout( tipsyTimer );
			} ).on( 'mouseout', function () {
				tipsyTimer = setTimeout( hideTipsy, timeout );
			} );

			// hide the tooltip when clicked on it
			$( '.uls-tipsy' ).on( 'click', hideTipsy );

			tipsyTimer = setTimeout( hideTipsy, timeout );
		}

		// remove any existing popups
		if ( ulsPopup ) {
			ulsPopup.$element.remove();
		}
		if ( ulsPosition === 'interlanguage' ) {
			if ( $ulsTrigger.offset().left > $( window ).width() / 2 ) {
				ulsPopupPosition = 'before';
			} else {
				ulsPopupPosition = 'after';
			}
			// Reverse for RTL
			if ( $( document.documentElement ).prop( 'dir' ) === 'rtl' ) {
				ulsPopupPosition = ( ulsPopupPosition === 'after' ) ? 'before' : 'after';
			}
		} else {
			ulsPopupPosition = 'below';
		}
		ulsPopup = new OO.ui.PopupWidget( {
			padded: true,
			width: 300,
			classes: [ 'uls-tipsy' ],
			// Automatically positioned relative to the trigger
			$floatableContainer: $ulsTrigger,
			position: ulsPopupPosition,
			$content: ( function () {
				var messageKey, $link;

				$link = $( '<a>' )
					.text( previousAutonym )
					.prop( {
						href: '',
						class: 'uls-prevlang-link',
						lang: previousLang,
						// We could get dir from uls.data,
						// but we are trying to avoid loading it
						// and 'auto' is safe enough in this context.
						// T130390: must use attr
						dir: 'auto'
					} )
					.on( 'click', function ( event ) {
						event.preventDefault();

						// Track if event logging is enabled
						mw.hook( 'mw.uls.language.revert' ).fire();

						mw.loader.using( [ 'ext.uls.common' ] ).then( function () {
							mw.uls.changeLanguage( event.target.lang );
						} );
					} );

				if ( mw.storage.get( 'uls-gp' ) === '1' ) {
					messageKey = 'ext-uls-undo-language-tooltip-text-local';
				} else {
					messageKey = 'ext-uls-undo-language-tooltip-text';
				}

				// Message keys listed above
				// eslint-disable-next-line mediawiki/msg-doc
				return $( '<p>' ).append( mw.message( messageKey, $link ).parseDom() );
			}() )
		} );

		ulsPopup.$element.appendTo( document.body );

		// The interlanguage position needs some time to settle down
		setTimeout( function () {
			// Show the tipsy tooltip on page load.
			showTipsy( 6000 );
		}, 700 );

		// manually show the tooltip
		$ulsTrigger.on( 'mouseover', function () {
			// show only if the ULS panel is not shown
			// eslint-disable-next-line no-jquery/no-sizzle
			if ( !$( '.uls-menu:visible' ).length ) {
				showTipsy( 3000 );
			}
		} );
	}

	/**
	 * Adds display and input settings to the ULS dialog after loading their code.
	 *
	 * @param {ULS} uls instance
	 */
	function loadDisplayAndInputSettings( uls ) {
		return mw.loader.using( languageSettingsModules ).then( function () {
			addDisplaySettings( uls );
			addInputSettings( uls );
		} );
	}

	function initInterface() {
		var $pLang,
			clickHandler,
			// T273928: No change to the heading should be made in modern Vector when the language button is present
			isButton = isUsingStandaloneLanguageButton(),
			$ulsTrigger = $( '.uls-trigger' ),
			anonMode = ( mw.user.isAnon() &&
				!mw.config.get( 'wgULSAnonCanChangeLanguage' ) ),
			ulsPosition = mw.config.get( 'wgULSPosition' );

		if ( ulsPosition === 'interlanguage' ) {
			// TODO: Refactor this block
			// The interlanguage links section.
			$pLang = $( '#p-lang' );
			// Add an element near the interlanguage links header
			$ulsTrigger = $( '<button>' )
				.addClass( 'uls-settings-trigger' );
			// Append ULS cog to languages section.
			$pLang.prepend( $ulsTrigger );
			// Take care of any other elements with this class.
			$ulsTrigger = $( '.uls-settings-trigger' );

			if ( !$pLang.find( 'div ul' ).children().length && isButton ) {
				// Replace the title of the interlanguage links area
				// if there are no interlanguage links
				$pLang.find( 'h3' )
					.text( mw.msg( 'uls-plang-title-languages' ) );
			}

			$ulsTrigger.attr( {
				title: mw.msg( 'ext-uls-select-language-settings-icon-tooltip' )
			} );

			clickHandler = function ( e, eventParams ) {
				var languagesettings = $ulsTrigger.data( 'languagesettings' ),
					languageSettingsOptions;

				if ( languagesettings ) {
					if ( !languagesettings.shown ) {
						mw.hook( 'mw.uls.settings.open' ).fire( eventParams && eventParams.source || 'interlanguage' );
					}

					return;
				}

				// Initialize the Language settings window
				languageSettingsOptions = {
					defaultModule: 'display',
					onPosition: function () {
						var caretRadius, top, left,
							ulsTriggerHeight = this.$element.height(),
							ulsTriggerWidth = this.$element[ 0 ].offsetWidth,
							ulsTriggerOffset = this.$element.offset();

						// Same as border width in mixins.less, or near enough
						caretRadius = 12;

						if ( ulsTriggerOffset.left > $( window ).width() / 2 ) {
							left = ulsTriggerOffset.left - this.$window.width() - caretRadius;
							this.$window.removeClass( 'selector-left' ).addClass( 'selector-right' );
						} else {
							left = ulsTriggerOffset.left + ulsTriggerWidth + caretRadius;
							this.$window.removeClass( 'selector-right' ).addClass( 'selector-left' );
						}

						// The top of the dialog is aligned in relation to
						// the middle of the trigger, so that middle of the
						// caret aligns with it. 16 is trigger icon height in pixels
						top = ulsTriggerOffset.top +
							( ulsTriggerHeight / 2 ) -
							( caretRadius + 16 );

						return { top: top, left: left };
					},
					onVisible: function () {
						this.$window.addClass( 'callout' );
					}
				};

				mw.loader.using( languageSettingsModules, function () {
					$ulsTrigger.languagesettings( languageSettingsOptions ).trigger( 'click' );
				} );

				e.stopPropagation();
			};
		} else if ( anonMode ) {
			clickHandler = function ( e, eventParams ) {
				var languagesettings = $ulsTrigger.data( 'languagesettings' );

				e.preventDefault();

				if ( languagesettings ) {
					if ( !languagesettings.shown ) {
						mw.hook( 'mw.uls.settings.open' ).fire( eventParams && eventParams.source || 'personal' );
					}
				} else {
					mw.loader.using( languageSettingsModules, function () {
						$ulsTrigger.languagesettings();

						$ulsTrigger.trigger( 'click', eventParams );
					} );
				}
			};
		} else {
			clickHandler = function ( e, eventParams ) {
				var uls = $ulsTrigger.data( 'uls' );

				e.preventDefault();

				if ( uls ) {
					if ( !uls.shown ) {
						mw.hook( 'mw.uls.settings.open' ).fire( eventParams && eventParams.source || 'personal' );
					}
				} else {
					mw.loader.using( 'ext.uls.mediawiki', function () {
						$ulsTrigger.uls( {
							quickList: function () {
								return mw.uls.getFrequentLanguageList();
							},
							onReady: function () {
								loadDisplayAndInputSettings( this );
							},
							onSelect: function ( language ) {
								mw.uls.changeLanguage( language );
							},
							// Not actually used on sites with the gear icon
							// in the interlanguage area, because this ULS
							// will be a container for other ULS panels.
							// However, this is used on sites with ULS
							// in the personal bar, and in that case it has the same
							// purpose as the selector in Display settings,
							// so it has the same identifier.
							ulsPurpose: 'interface-language'
						} );

						// Allow styles to apply first and position to work by
						// delaying the activation after them.
						setTimeout( function () {
							$ulsTrigger.trigger( 'click', eventParams );
						}, 0 );
					} );
				}
			};
		}

		$ulsTrigger.on( 'click', clickHandler );

		// Bind language settings to preferences page link
		$( '#uls-preferences-link' )
			.on( 'click keypress', function ( e ) {
				if (
					e.type === 'click' ||
					e.type === 'keypress' && e.which === 13
				) {
					$ulsTrigger.trigger( 'click', {
						source: 'preferences'
					} );
				}

				return false;
			} );
	}

	function initTooltip() {
		var previousLanguage, currentLanguage, previousAutonym, currentAutonym;

		if ( !userCanChangeLanguage() ) {
			return;
		}

		previousLanguage = mw.storage.get( 'uls-previous-language-code' );
		currentLanguage = mw.config.get( 'wgUserLanguage' );
		previousAutonym = mw.storage.get( 'uls-previous-language-autonym' );
		currentAutonym = mw.config.get( 'wgULSCurrentAutonym' );

		// If storage is empty, i.e. first visit, then store the current language
		// immediately so that we know when it changes.
		if ( !previousLanguage || !previousAutonym ) {
			mw.storage.set( 'uls-previous-language-code', currentLanguage );
			mw.storage.set( 'uls-previous-language-autonym', currentAutonym );
			return;
		}

		if ( previousLanguage !== currentLanguage ) {
			mw.loader.using( 'oojs-ui-core' ).done( function () {
				showUndoTooltip( previousLanguage, previousAutonym );
			} );
			mw.storage.set( 'uls-previous-language-code', currentLanguage );
			mw.storage.set( 'uls-previous-language-autonym', currentAutonym );
			// Store this language in a list of frequently used languages
			mw.loader.using( [ 'ext.uls.common' ] ).then( function () {
				mw.uls.addPreviousLanguage( currentLanguage );
			} );
		}
	}

	function initIme() {
		var imeSelector = mw.config.get( 'wgULSImeSelectors' ).join( ', ' );

		$( document.body ).on( 'focus.imeinit', imeSelector, function () {
			var $input = $( this );
			$( document.body ).off( '.imeinit' );
			mw.loader.using( 'ext.uls.ime', function () {
				mw.ime.setup();
				mw.ime.handleFocus( $input );
			} );
		} );
	}

	/**
	 * Load and open ULS for content language selection.
	 *
	 * This dialog is primarily for selecting the language of the content, but may also provide
	 * access to display and input settings if isUsingStandaloneLanguageButton() returns true.
	 *
	 * @param {jQuery.Event} ev
	 */
	function loadContentLanguageSelector( ev ) {
		ev.preventDefault();

		mw.loader.using( 'ext.uls.mediawiki' ).then( function () {
			var $target, parent, languageNodes, standalone, uls;

			$target = $( ev.target );
			parent = document.querySelectorAll( '.mw-portlet-lang, #p-lang' )[ 0 ];
			languageNodes = parent ? parent.querySelectorAll( '.interlanguage-link-target' ) : [];
			standalone = isUsingStandaloneLanguageButton();

			// Setup click handler for ULS
			launchULS(
				$target,
				mw.uls.getInterlanguageListFromNodes( languageNodes ),
				// Using this as heuristic for now. May need to reconsider later. Enables
				// behavior sepcific to compact language links.
				!standalone
			);

			// Trigger the click handler to open ULS once ready
			if ( standalone ) {
				// Provide access to display and input settings if this entry point is the single point
				// of access to all language settings.
				uls = $target.data( 'uls' );
				loadDisplayAndInputSettings( uls ).always( function () {
					$target.trigger( 'click' );
				} );
			} else {
				$target.trigger( 'click' );
			}
		} );
	}

	/** Setup lazy-loading for content language selector */
	function initContentLanguageSelectorClickHandler() {
		// FIXME: In Timeless ULS is embedded in a menu which stops event propagation
		if ( $( '.sidebar-inner' ).length ) {
			$( '.sidebar-inner #p-lang' )
				.one( 'click', '.mw-interlanguage-selector', loadContentLanguageSelector );
		} else {
			// This button may be created by the new Vector skin, or ext.uls.compactlinks module
			// if there are many languages. Warning: Both this module and ext.uls.compactlinks
			// module may run simultaneously. Using event delegation to avoid race conditions where
			// the trigger may be created after this code.
			$( document ).one( 'click', '.mw-interlanguage-selector', loadContentLanguageSelector );
		}
	}

	function init() {
		initInterface();
		initTooltip();
		initIme();
		initContentLanguageSelectorClickHandler();
	}

	// Early execute of init
	if ( document.readyState === 'interactive' ) {
		init();
	} else {
		$( init );
	}

}() );
