'use strict';
// Disable DOM state lint rule, the purpose of this class is to capture actual state.
/* eslint-disable no-jquery/no-class-state */

let finalExitEvent = null;
const COLUMN_COPY = 'c',
	COLUMN_OTHER = 'o',
	COLUMN_YOUR = 'y',
	PACK_VERSION = 'v1:',
	ROW_SEPARATOR = '|',
	SELECTION_INCOMPLETE = '?',
	SELECTION_NONEXISTENT = '',
	SELECTION_OTHER = '<',
	SELECTION_YOUR = '>',
	TEXT_CHANGED = '+',
	TEXT_UNCHANGED = '';

function wasEdited( $column ) {
	const $editor = $column.find( '.mw-twocolconflict-split-editor' );
	return $editor.length && $editor.val() !== $editor[ 0 ].defaultValue;
}

function RowFormatter() {}

RowFormatter.formatView = function ( $view ) {
	const $rows = $view.find(
		'.mw-twocolconflict-single-row, ' +
		'.mw-twocolconflict-split-row'
	);

	return PACK_VERSION + RowFormatter.formatRows( $rows );
};

RowFormatter.formatRows = function ( $rows ) {
	const formattedRows = $rows.get().map( RowFormatter.formatRow );

	return formattedRows.join( ROW_SEPARATOR );
};

RowFormatter.formatRow = function ( el ) {
	const $row = $( el ),
		$columns = $row.find(
			'.mw-twocolconflict-single-column, ' +
			'.mw-twocolconflict-split-column'
		);

	if ( $columns.length === 1 ) {
		return RowFormatter.formatColumn( $columns[ 0 ] );
	} else if ( $columns.length === 2 ) {
		return RowFormatter.formatColumn( $columns[ 0 ] ) +
			RowFormatter.formatSelection( $row ) +
			RowFormatter.formatColumn( $columns[ 1 ] );
	}
	return '';
};

RowFormatter.formatSelection = function ( $row ) {
	const $inputs = $row.find( 'input[type="radio"]' ),
		selectionName = $inputs.filter( ':checked' ).first().val();

	if ( $inputs.length === 0 ) {
		return SELECTION_NONEXISTENT;
	} else if ( selectionName === 'other' ) {
		return SELECTION_OTHER;
	} else if ( selectionName === 'your' ) {
		return SELECTION_YOUR;
	}
	// Nothing recognizable was selected, yet inputs are present.
	return SELECTION_INCOMPLETE;
};

RowFormatter.formatColumn = function ( el ) {
	const $column = $( el );
	let out = '';
	if ( $column.hasClass( 'mw-twocolconflict-split-delete' ) ) {
		out += COLUMN_OTHER;
	} else if ( $column.hasClass( 'mw-twocolconflict-split-add' ) ) {
		out += COLUMN_YOUR;
	} else if ( $column.hasClass( 'mw-twocolconflict-split-copy' ) ) {
		out += COLUMN_COPY;
	}

	if ( wasEdited( $column ) ) {
		out += TEXT_CHANGED;
	} else {
		out += TEXT_UNCHANGED;
	}

	return out;
};

function buildRowStatistics() {
	const $view = $( '.mw-twocolconflict-split-view' );

	return RowFormatter.formatView( $view );
}

function recordExitStatistics() {
	/* eslint-disable camelcase */
	mw.track( 'event.TwoColConflictExit', {
		action: finalExitEvent || 'unknown',
		start_time_ts_ms: parseInt( $( 'input[name="wpStarttime"]' ).val() ) * 1000 || 0,
		base_rev_id: parseInt( $( 'input[name="parentRevId"]' ).val() ),
		latest_rev_id: parseInt( $( 'input[name="editRevId"]' ).val() ),
		page_namespace: parseInt( mw.config.get( 'wgNamespaceNumber' ) ),
		page_title: mw.config.get( 'wgTitle' ),
		selections: buildRowStatistics(),
		session_token: mw.user.sessionId()
	} );
	/* eslint-enable camelcase */
}

function initTrackingListeners() {
	$( '#wpSave' ).on( 'click', () => {
		finalExitEvent = 'save';
	} );

	$( '#mw-editform-cancel' ).on( 'click', () => {
		finalExitEvent = 'cancel';
	} );

	window.addEventListener( 'unload', recordExitStatistics );
}

module.exports = {
	initTrackingListeners: initTrackingListeners,

	private: {
		RowFormatter: RowFormatter
	}
};
