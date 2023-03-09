'use strict';

var UtilModule = require( 'ext.TwoColConflict.Util' );

/**
 * @param {jQuery} $column
 * @return {OO.ui.Element}
 */
function getColumnEditButton( $column ) {
	return OO.ui.ButtonWidget.static.infuse(
		$column.find( '.mw-twocolconflict-split-edit-button' )
	);
}

/**
 * @param {jQuery} $column
 */
function disableColumn( $column ) {
	getColumnEditButton( $column )
		.toggle( false );
	$column
		.removeClass( 'mw-twocolconflict-split-selected' )
		.addClass( 'mw-twocolconflict-split-unselected' );
}

/**
 * @param {jQuery} $column
 */
function enableColumn( $column ) {
	getColumnEditButton( $column )
		.toggle( true );
	$column
		.addClass( 'mw-twocolconflict-split-selected' )
		.removeClass( 'mw-twocolconflict-split-unselected' );
}

function getSelectedColumn( $element ) {
	return $element.find(
		'.mw-twocolconflict-single-column, ' +
		'.mw-twocolconflict-split-column.mw-twocolconflict-split-copy, ' +
		'.mw-twocolconflict-split-column.mw-twocolconflict-split-selected' );
}

/**
 * @return {string}
 */
function getEditorFontClass() {
	return $( '.mw-twocolconflict-split-editor' ).attr( 'class' )
		.replace( 'mw-twocolconflict-split-editor', '' )
		.trim();
}

function expandText( $row ) {
	$row.find( '.mw-twocolconflict-split-collapsed' )
		.removeClass( 'mw-twocolconflict-split-collapsed' )
		.addClass( 'mw-twocolconflict-split-expanded' );
}

function collapseText( $row ) {
	$row.find( '.mw-twocolconflict-split-expanded' )
		.removeClass( 'mw-twocolconflict-split-expanded' )
		.addClass( 'mw-twocolconflict-split-collapsed' );
}

/**
 * @param {jQuery} $row
 */
function enableEditing( $row ) {
	var $selected = getSelectedColumn( $row ),
		originalHeight = $selected.find( '.mw-twocolconflict-split-editable' ).height();

	expandText( $row );
	$row.addClass( 'mw-twocolconflict-split-editing' );
	// The following classes are used here:
	// * mw-editfont-monospace
	// * mw-editfont-sans-serif
	// * mw-editfont-serif
	$row.find( '.mw-twocolconflict-split-editable' ).addClass( getEditorFontClass() );

	$selected.find( 'textarea' ).each( function () {
		var $editor = $( this );
		if ( $editor.height() < originalHeight ) {
			$editor.height( originalHeight );
		}
	} );

	$selected.find( '.mw-twocolconflict-split-editor' ).focus();
}

/**
 * @param {jQuery} $row
 */
function disableEditing( $row ) {
	$row.removeClass( 'mw-twocolconflict-split-editing' );
	// The following classes are used here:
	// * mw-editfont-monospace
	// * mw-editfont-sans-serif
	// * mw-editfont-serif
	$row.find( '.mw-twocolconflict-split-editable' ).removeClass( getEditorFontClass() );
}

/**
 * @param {jQuery} $row
 */
function saveEditing( $row ) {
	var $selected = getSelectedColumn( $row ),
		$editor = $selected.find( '.mw-twocolconflict-split-editor' ),
		$diffText = $selected.find( '.mw-twocolconflict-split-difftext' );

	if ( !$editor.length || $editor.val() === $editor[ 0 ].defaultValue ) {
		var $resetDiffText = $selected.find( '.mw-twocolconflict-split-reset-diff-text' );
		$diffText.html( $resetDiffText.html() );
	} else {
		$diffText.text( $editor.val() );
	}

	disableEditing( $row );
}

/**
 * @param {jQuery} $row
 */
function resetWarning( $row ) {
	var $selected = getSelectedColumn( $row ),
		$editor = $selected.find( '.mw-twocolconflict-split-editor' ),
		originalText = $editor[ 0 ].defaultValue;

	// The later merge ignores trailing newlines, they don't cause a change
	if ( !$editor.length ||
		$editor.val().replace( /[\r\n]+$/, '' ) === originalText.replace( /[\r\n]+$/, '' )
	) {
		disableEditing( $row );
		return;
	}

	OO.ui.confirm(
		mw.msg( 'twocolconflict-split-reset-warning' ),
		{
			actions: [
				{
					label: mw.msg( 'twocolconflict-split-reset-warning-cancel' ),
					action: 'cancel'
				},
				{
					label: mw.msg( 'twocolconflict-split-reset-warning-accept' ),
					action: 'accept'
				}
			]
		}
	).done( function ( confirmed ) {
		if ( confirmed ) {
			var $diffText = $selected.find( '.mw-twocolconflict-split-difftext' ),
				$resetDiffText = $selected.find( '.mw-twocolconflict-split-reset-diff-text' );

			$editor.val( originalText );
			$diffText.html( $resetDiffText.html() );
			disableEditing( $row );
		}
	} );
}

function initButtonEvents() {
	[
		{ selector: '.mw-twocolconflict-split-edit-button', onclick: enableEditing },
		{ selector: '.mw-twocolconflict-split-save-button', onclick: saveEditing },
		{ selector: '.mw-twocolconflict-split-reset-button', onclick: resetWarning },
		{ selector: '.mw-twocolconflict-split-expand-button', onclick: expandText },
		{ selector: '.mw-twocolconflict-split-collapse-button', onclick: collapseText }
	].forEach( function ( button ) {
		$( button.selector ).each( function () {
			var widget = OO.ui.ButtonWidget.static.infuse( this ),
				$row = widget.$element.closest( '.mw-twocolconflict-single-row, .mw-twocolconflict-split-row' );

			widget.on( 'click', function () {
				button.onclick( $row );
			} );
		} );
	} );
}

function isEditableSingleColumn( $column ) {
	return $column.is( '.mw-twocolconflict-single-column.mw-twocolconflict-split-add' );
}

function initColumnClickEvent() {
	$( '.mw-twocolconflict-split-column, .mw-twocolconflict-single-column' ).each( function () {
		var $column = $( this ),
			$row = $column.closest( '.mw-twocolconflict-single-row, .mw-twocolconflict-split-row' );

		$column.on( 'click', function () {
			if (
				( $column.is( '.mw-twocolconflict-split-selected' ) || isEditableSingleColumn( $column ) ) &&
				!$row.is( '.mw-twocolconflict-split-editing' )
			) {
				enableEditing( $row );
			}
		} );
	} );
}

function resetHeaderSideSelector( selectedValue ) {
	var $headerSelection = $( '.mw-twocolconflict-split-selection-header' ),
		$selectedSide = $headerSelection.find( 'input:checked' );

	if ( $selectedSide.val() !== selectedValue ) {
		$selectedSide.prop( 'checked', false );
		$selectedSide.prop( 'title', mw.msg(
			selectedValue === 'other' ?
				'twocolconflict-split-select-all-your-tooltip' :
				'twocolconflict-split-select-all-other-tooltip'
		) );
	}
}

function initHeaderSideSelector() {
	var $headerSelection = $( '.mw-twocolconflict-split-selection-header' );

	$headerSelection.find( 'input' ).on( 'change', function () {
		var $selectedHeaderSide = $( this ),
			$unselectedHeaderSide = $headerSelection.find( 'input:not(:checked)' );
		$selectedHeaderSide.prop( 'title', mw.msg(
			$selectedHeaderSide.val() === 'other' ?
				'twocolconflict-split-selected-all-other-tooltip' :
				'twocolconflict-split-selected-all-your-tooltip'
		) );
		$unselectedHeaderSide.prop( 'title', mw.msg(
			$unselectedHeaderSide.val() === 'other' ?
				'twocolconflict-split-select-all-other-tooltip' :
				'twocolconflict-split-select-all-your-tooltip'
		) );

		$( '.mw-twocolconflict-split-selection-row input' ).each( function () {
			var $rowButton = $( this );
			if ( $rowButton.val() === $selectedHeaderSide.val() ) {
				$rowButton.click();
			}
		} );
	} );
}

function handleSelectColumn() {
	var $row = $( this ).closest( '.mw-twocolconflict-split-row' ),
		$selected = $row.find( '.mw-twocolconflict-split-selection-row input:checked' ),
		$unselected = $row.find( '.mw-twocolconflict-split-selection-row input:not(:checked)' ),
		$label = $row.find( '.mw-twocolconflict-split-selector-label span' ),
		// TODO: Rename classes, "add" should be "your", etc.
		$yourColumn = $row.find( '.mw-twocolconflict-split-add' ),
		$otherColumn = $row.find( '.mw-twocolconflict-split-delete' );

	if ( $selected.val() === 'your' ) {
		disableColumn( $otherColumn );
		enableColumn( $yourColumn );
		$selected.prop( 'title', mw.msg( 'twocolconflict-split-selected-your-tooltip' ) );
		$unselected.prop( 'title', mw.msg( 'twocolconflict-split-select-other-tooltip' ) );
		$row.removeClass( 'mw-twocolconflict-no-selection' );
		$label.text( mw.msg( 'twocolconflict-split-your-version-chosen' ) );
	} else if ( $selected.val() === 'other' ) {
		enableColumn( $otherColumn );
		disableColumn( $yourColumn );
		$selected.prop( 'title', mw.msg( 'twocolconflict-split-selected-other-tooltip' ) );
		$unselected.prop( 'title', mw.msg( 'twocolconflict-split-select-your-tooltip' ) );
		$row.removeClass( 'mw-twocolconflict-no-selection' );
		$label.text( mw.msg( 'twocolconflict-split-other-version-chosen' ) );
	} else {
		disableColumn( $otherColumn );
		disableColumn( $yourColumn );
		$label.text( mw.msg( 'twocolconflict-split-choose-version' ) );
	}

	resetHeaderSideSelector( $selected.val() );
}

function initRowSideSelectors() {
	var $rowSwitches = $( '.mw-twocolconflict-split-selection-row' ),
		$radioButtons = $rowSwitches.find( 'input' );

	// TODO remove when having no selection is the default
	$radioButtons.prop( 'checked', false );
	$radioButtons.on( 'change', handleSelectColumn );

	$rowSwitches.find( 'input:first-of-type' ).trigger( 'change' );
}

function showPreview( parsedContent, parsedNote ) {
	$( '#wikiPreview' ).remove();
	var $html = $( 'html' );

	var $note = $( '<div>' )
		.addClass( 'previewnote' )
		.append(
			$( '<h2>' )
				.attr( 'id', 'mw-previewheader' )
				.append( mw.msg( 'preview' ) ),
			$( '<div>' )
				.addClass( [ 'mw-message-box', 'mw-message-box-warning' ] )
				.append( $( parsedNote ).children() )
		);

	// The following classes are used here:
	// * mw-content-ltr
	// * mw-content-rtl
	var $content = $( '<div>' )
		.addClass( 'mw-content-' + $html.attr( 'dir' ) )
		.attr( 'dir', $html.attr( 'dir' ) )
		.attr( 'lang', $html.attr( 'lang' ) )
		.append( parsedContent );

	var $preview = $( '<div>' )
		.attr( 'id', 'wikiPreview' )
		.addClass( 'ontop' );

	$( '#mw-content-text' ).prepend(
		$preview.append( $note, $content )
	);

	$( 'html, body' ).animate( { scrollTop: $( '#top' ).offset().top }, 500 );
}

function validateForm() {
	var isFormValid = true;

	$( '.mw-twocolconflict-split-selection-row' ).each( function () {
		var $row = $( this ).closest( '.mw-twocolconflict-split-row' ),
			$checked = $row.find( 'input:checked' );

		if ( $checked.length ) {
			$row.removeClass( 'mw-twocolconflict-no-selection' );
		} else {
			$row.addClass( 'mw-twocolconflict-no-selection' );
			isFormValid = false;
		}
	} );

	return isFormValid;
}

function initPreview() {
	var $previewBtn = $( '#wpPreviewWidget' );
	if ( !$previewBtn.length ) {
		return;
	}

	var api = new mw.Api();

	OO.ui.infuse( $previewBtn )
		.setDisabled( false );

	$( '#wpPreview' ).click( function ( e ) {
		e.preventDefault();

		if ( !validateForm() ) {
			return;
		}

		var arrow = $( 'html' ).attr( 'dir' ) === 'rtl' ? '←' : '→',
			title = mw.config.get( 'wgPageName' );

		$.when(
			api.parse(
				UtilModule.Merger( getSelectedColumn( $( '.mw-twocolconflict-split-view' ) ) ),
				{
					title: title,
					prop: 'text',
					pst: true,
					disablelimitreport: true,
					disableeditsection: true
				}
			),
			api.parse(
				'{{int:previewnote}} <span class="mw-continue-editing">[[#editform|' +
					arrow + ' {{int:continue-editing}}]]</span>',
				{
					title: title,
					prop: 'text',
					disablelimitreport: true,
					disableeditsection: true
				}
			)
		).done( function ( parsedContent, parsedNote ) {
			showPreview( parsedContent, parsedNote );
		} );
	} );
}

function initSubmit() {
	$( '#wpSave' ).click( function ( e ) {
		if ( !validateForm() ) {
			e.preventDefault();
		}
	} );
}

function initSwapHandling() {
	var $swapButton = $( '.mw-twocolconflict-single-swap-button' );
	if ( !$swapButton.length ) {
		return;
	}

	function getRowNumber( $column ) {
		return $column.find( 'textarea[name^="mw-twocolconflict-split-content"]' )
			.attr( 'name' )
			.match( /\d+/ )[ 0 ];
	}

	function setRowNumber( $column, oldRowNum, newRowNum ) {
		$column.find( 'input, textarea' ).each( function ( index, input ) {
			input.name = input.name.replace( '[' + oldRowNum + ']', '[' + newRowNum + ']' );
		} );
	}

	OO.ui.ButtonWidget.static.infuse( $swapButton ).on( 'click', function () {
		var $rowContainer = $( '.mw-twocolconflict-single-column-rows' ),
			$rows = $rowContainer.find( '.mw-twocolconflict-conflicting-talk-row' ),
			$buttonContainer = $rowContainer.find( '.mw-twocolconflict-single-swap-button-container' ),
			$upper = $rows.eq( 0 ),
			$lower = $rows.eq( 1 ),
			upperRowNum = getRowNumber( $upper ),
			lowerRowNum = getRowNumber( $lower );

		setRowNumber( $upper, upperRowNum, lowerRowNum );
		setRowNumber( $lower, lowerRowNum, upperRowNum );
		$rowContainer[ 0 ].insertBefore( $lower[ 0 ], $upper[ 0 ] );
		$rowContainer[ 0 ].insertBefore( $buttonContainer[ 0 ], $upper[ 0 ] );
	} );
}

/**
 * Expose an action to copy the entire wikitext source of "your" originally submitted revision.
 */
function initSourceCopy() {
	var $copyLink = $( '.mw-twocolconflict-copy-link a' ),
		wasClicked = false,
		$confirmPopup, popupTimeout;
	if ( !$copyLink.length ) {
		return;
	}

	$confirmPopup = new OO.ui.PopupWidget( {
		$content: $( '<p>' ).text( mw.msg( 'twocolconflict-copy-notice' ) ),
		$floatableContainer: $copyLink,
		position: 'above',
		align: 'forwards',
		anchor: false,
		autoClose: true,
		classes: [ 'mw-twocolconflict-copy-notice' ]
	} );
	$( 'body' ).append( $confirmPopup.$element );

	$copyLink.text( mw.msg( 'twocolconflict-copy-action' ) )
		.attr( 'title', mw.msg( 'twocolconflict-copy-tooltip' ) );
	$copyLink.click( function () {
		$( '.mw-twocolconflict-your-text' ).select();
		document.execCommand( 'copy' );

		if ( !wasClicked ) {
			// only count once if the user clicks several times from the same screen
			mw.track( 'counter.MediaWiki.TwoColConflict.copy.jsclick' );
			wasClicked = true;
		}

		$confirmPopup.toggle( true );
		popupTimeout = setTimeout( function () {
			$confirmPopup.toggle( false );
		}, 5000 );

		return false;
	} );

	$confirmPopup.on( 'toggle', function () {
		clearTimeout( popupTimeout );
	} );
}

$( function () {
	var $coreHintCheckbox = $( '.mw-twocolconflict-core-ui-hint input[ type="checkbox" ]' );
	if ( $coreHintCheckbox.length ) {
		$coreHintCheckbox.change( function () {
			if ( this.checked ) {
				( new mw.Api() ).saveOption( 'userjs-twocolconflict-hide-core-hint', '1' );
			}
		} );
		// When the hint element exists, the split view does not, and nothing below applies
		return;
	}

	var initTracking = UtilModule.Tracking.initTrackingListeners,
		initTour = require( './ext.TwoColConflict.Split.Tour.js' );

	// disable all javascript from this feature when testing the nojs implementation
	if ( mw.cookie.get( '-twocolconflict-test-nojs', 'mw' ) ) {
		// set CSS class so nojs CSS rules are applied
		$( 'html' ).removeClass( 'client-js' ).addClass( 'client-nojs' );
		return;
	}

	initRowSideSelectors();
	initHeaderSideSelector();
	initColumnClickEvent();
	initButtonEvents();
	initSwapHandling();
	initPreview();
	initSubmit();
	initTour();
	initTracking();
	initSourceCopy();
} );
