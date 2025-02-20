'use strict';

const Merger = require( './ext.TwoColConflict.Split.Merger.js' );
const Tracking = require( './ext.TwoColConflict.Split.tracking.js' );

/**
 * @param {jQuery} $column
 * @param {boolean} [enabled=true]
 */
function enableColumn( $column, enabled ) {
	enabled = enabled !== false;
	const $editButton = $column.find( '.mw-twocolconflict-split-edit-button' );
	const editButton = OO.ui.ButtonWidget.static.infuse( $editButton );
	editButton.toggle( enabled );
	$column.toggleClass( 'mw-twocolconflict-split-selected', enabled )
		.toggleClass( 'mw-twocolconflict-split-unselected', !enabled );
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
	const $selected = getSelectedColumn( $row );
	const originalHeight = $selected.find( '.mw-twocolconflict-split-editable' ).height();

	expandText( $row );
	$row.addClass( 'mw-twocolconflict-split-editing' );
	// The following classes are used here:
	// * mw-editfont-monospace
	// * mw-editfont-sans-serif
	// * mw-editfont-serif
	$row.find( '.mw-twocolconflict-split-editable' ).addClass( getEditorFontClass() );

	$selected.find( 'textarea' ).each( function () {
		const $editor = $( this );
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
	const $selected = getSelectedColumn( $row );
	const $editor = $selected.find( '.mw-twocolconflict-split-editor' );
	const $diffText = $selected.find( '.mw-twocolconflict-split-difftext' );

	if ( !$editor.length || $editor.val() === $editor[ 0 ].defaultValue ) {
		const $resetDiffText = $selected.find( '.mw-twocolconflict-split-reset-diff-text' );
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
	const $selected = getSelectedColumn( $row );
	const $editor = $selected.find( '.mw-twocolconflict-split-editor' );
	const originalText = $editor[ 0 ].defaultValue;

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
	).done( ( confirmed ) => {
		if ( confirmed ) {
			const $diffText = $selected.find( '.mw-twocolconflict-split-difftext' );
			const $resetDiffText = $selected.find( '.mw-twocolconflict-split-reset-diff-text' );

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
	].forEach( ( button ) => {
		$( button.selector ).each( function () {
			const widget = OO.ui.ButtonWidget.static.infuse( this );
			const $row = widget.$element.closest( '.mw-twocolconflict-single-row, .mw-twocolconflict-split-row' );

			widget.on( 'click', () => {
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
		const $column = $( this );
		const $row = $column.closest( '.mw-twocolconflict-single-row, .mw-twocolconflict-split-row' );

		$column.on( 'click', () => {
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
	const $headerSelection = $( '.mw-twocolconflict-split-selection-header' );
	const $selectedSide = $headerSelection.find( 'input:checked' );

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
	const $headerSelection = $( '.mw-twocolconflict-split-selection-header' );

	$headerSelection.find( 'input' ).on( 'change', function () {
		const $selectedHeaderSide = $( this );
		const $unselectedHeaderSide = $headerSelection.find( 'input:not(:checked)' );
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
			const $rowButton = $( this );
			if ( $rowButton.val() === $selectedHeaderSide.val() ) {
				$rowButton.click();
			}
		} );
	} );
}

function handleSelectColumn() {
	const $row = $( this ).closest( '.mw-twocolconflict-split-row' );
	const $selected = $row.find( '.mw-twocolconflict-split-selection-row input:checked' );
	const $unselected = $row.find( '.mw-twocolconflict-split-selection-row input:not(:checked)' );
	const $label = $row.find( '.mw-twocolconflict-split-selector-label span' );
	// TODO: Rename classes, "add" should be "your", etc.
	const $yourColumn = $row.find( '.mw-twocolconflict-split-add' );
	const $otherColumn = $row.find( '.mw-twocolconflict-split-delete' );

	if ( $selected.val() === 'your' ) {
		enableColumn( $otherColumn, false );
		enableColumn( $yourColumn );
		$selected.prop( 'title', mw.msg( 'twocolconflict-split-selected-your-tooltip' ) );
		$unselected.prop( 'title', mw.msg( 'twocolconflict-split-select-other-tooltip' ) );
		$row.removeClass( 'mw-twocolconflict-no-selection' );
		$label.text( mw.msg( 'twocolconflict-split-your-version-chosen' ) );
	} else if ( $selected.val() === 'other' ) {
		enableColumn( $otherColumn );
		enableColumn( $yourColumn, false );
		$selected.prop( 'title', mw.msg( 'twocolconflict-split-selected-other-tooltip' ) );
		$unselected.prop( 'title', mw.msg( 'twocolconflict-split-select-your-tooltip' ) );
		$row.removeClass( 'mw-twocolconflict-no-selection' );
		$label.text( mw.msg( 'twocolconflict-split-other-version-chosen' ) );
	} else {
		enableColumn( $otherColumn, false );
		enableColumn( $yourColumn, false );
		$label.text( mw.msg( 'twocolconflict-split-choose-version' ) );
	}

	resetHeaderSideSelector( $selected.val() );
}

function initRowSideSelectors() {
	const $rowSwitches = $( '.mw-twocolconflict-split-selection-row' );
	const $radioButtons = $rowSwitches.find( 'input' );

	// TODO remove when having no selection is the default
	$radioButtons.prop( 'checked', false );
	$radioButtons.on( 'change', handleSelectColumn );

	$rowSwitches.find( 'input:first-of-type' ).trigger( 'change' );
}

function showPreview( parsedContent, parsedNote ) {
	$( '#wikiPreview' ).remove();
	const $html = $( 'html' );
	const noteContentElement = $( '<div>' )
		.append( $( parsedNote ).children() )[ 0 ];
	const $note = $( '<div>' )
		.addClass( 'previewnote' )
		.append(
			$( '<h2>' )
				.attr( 'id', 'mw-previewheader' )
				.append( mw.msg( 'preview' ) ),
			mw.util.messageBox( noteContentElement, 'warning' )
		);

	// The following classes are used here:
	// * mw-content-ltr
	// * mw-content-rtl
	const $content = $( '<div>' )
		.addClass( 'mw-content-' + $html.attr( 'dir' ) )
		.attr( 'dir', $html.attr( 'dir' ) )
		.attr( 'lang', $html.attr( 'lang' ) )
		.append( parsedContent );

	const $preview = $( '<div>' )
		.attr( 'id', 'wikiPreview' )
		.addClass( 'ontop' );

	$( '#mw-content-text' ).prepend(
		$preview.append( $note, $content )
	);

	$( 'html, body' ).animate( { scrollTop: $( '#content' ).offset().top }, 500 );
}

function validateForm() {
	let isFormValid = true;

	$( '.mw-twocolconflict-split-selection-row' ).each( function () {
		const $row = $( this ).closest( '.mw-twocolconflict-split-row' );
		const $checked = $row.find( 'input:checked' );

		$row.toggleClass( 'mw-twocolconflict-no-selection', !$checked.length );
		if ( !$checked.length ) {
			isFormValid = false;
		}
	} );

	return isFormValid;
}

function initPreview() {
	const $previewBtn = $( '#wpPreviewWidget' );
	if ( !$previewBtn.length ) {
		return;
	}

	const api = new mw.Api();

	OO.ui.infuse( $previewBtn )
		.setDisabled( false );

	$( '#wpPreview' ).click( ( e ) => {
		e.preventDefault();

		if ( !validateForm() ) {
			return;
		}

		const arrow = $( 'html' ).attr( 'dir' ) === 'rtl' ? '←' : '→';
		const title = mw.config.get( 'wgPageName' );

		$.when(
			api.parse(
				Merger( getSelectedColumn( $( '.mw-twocolconflict-split-view' ) ) ),
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
		).done( ( parsedContent, parsedNote ) => {
			showPreview( parsedContent, parsedNote );
		} );
	} );
}

function initSubmit() {
	$( '#wpSave' ).click( ( e ) => {
		if ( !validateForm() ) {
			e.preventDefault();
		}
	} );
}

function initSwapHandling() {
	const $swapButton = $( '.mw-twocolconflict-single-swap-button' );
	if ( !$swapButton.length ) {
		return;
	}

	function getRowNumber( $column ) {
		return $column.find( 'textarea[name^="mw-twocolconflict-split-content"]' )
			.attr( 'name' )
			.match( /\d+/ )[ 0 ];
	}

	function setRowNumber( $column, oldRowNum, newRowNum ) {
		$column.find( 'input, textarea' ).each( ( index, input ) => {
			input.name = input.name.replace( '[' + oldRowNum + ']', '[' + newRowNum + ']' );
		} );
	}

	OO.ui.ButtonWidget.static.infuse( $swapButton ).on( 'click', () => {
		const $rowContainer = $( '.mw-twocolconflict-single-column-rows' );
		const $rows = $rowContainer.find( '.mw-twocolconflict-conflicting-talk-row' );
		const $buttonContainer = $rowContainer.find( '.mw-twocolconflict-single-swap-button-container' );
		const $upper = $rows.eq( 0 );
		const $lower = $rows.eq( 1 );
		const upperRowNum = getRowNumber( $upper );
		const lowerRowNum = getRowNumber( $lower );

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
	const $copyLink = $( '.mw-twocolconflict-copy-link a' );
	let wasClicked = false;
	let popupTimeout;
	if ( !$copyLink.length ) {
		return;
	}

	const $confirmPopup = new OO.ui.PopupWidget( {
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
	$copyLink.click( () => {
		$( '.mw-twocolconflict-your-text' ).select();
		document.execCommand( 'copy' );

		if ( !wasClicked ) {
			// only count once if the user clicks several times from the same screen
			mw.track( 'counter.MediaWiki.TwoColConflict.copy.jsclick' );
			wasClicked = true;
		}

		$confirmPopup.toggle( true );
		popupTimeout = setTimeout( () => {
			$confirmPopup.toggle( false );
		}, 5000 );

		return false;
	} );

	$confirmPopup.on( 'toggle', () => {
		clearTimeout( popupTimeout );
	} );
}

if ( !window.QUnit ) {
	$( () => {
		const $coreHintCheckbox = $( '.mw-twocolconflict-core-ui-hint input[ type="checkbox" ]' );
		if ( $coreHintCheckbox.length ) {
			$coreHintCheckbox.change( function () {
				if ( this.checked && mw.user.isNamed() ) {
					( new mw.Api() ).saveOption( 'userjs-twocolconflict-hide-core-hint', '1' );
				}
			} );
			// When the hint element exists, the split view does not, and nothing below applies
			return;
		}

		const initTracking = Tracking.initTrackingListeners;
		const initTour = require( './ext.TwoColConflict.Split.Tour.js' );

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
}

module.exports = { private: { Merger, RowFormatter: Tracking.private.RowFormatter } };
