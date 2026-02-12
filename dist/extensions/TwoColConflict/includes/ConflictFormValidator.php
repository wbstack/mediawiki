<?php

namespace TwoColConflict;

use MediaWiki\Request\WebRequest;

/**
 * @license GPL-2.0-or-later
 */
class ConflictFormValidator {

	/**
	 * Check whether inputs are valid.  Note that a POST without conflict fields is considered
	 * valid.
	 *
	 * @param WebRequest $request
	 * @return bool True when valid
	 */
	public function validateRequest( WebRequest $request ): bool {
		$contentRows = $request->getArray( 'mw-twocolconflict-split-content' );
		if ( $contentRows === null ) {
			// Not a conflict form.
			return true;
		}
		if ( $contentRows === [] ) {
			// Empty conflict form is bad.
			return false;
		}

		$sideSelection = $request->getArray( 'mw-twocolconflict-side-selector', [] );
		if ( $sideSelection ) {
			return $this->validateSideSelection( $contentRows, $sideSelection );
		}

		if ( $request->getBool( 'mw-twocolconflict-single-column-view' ) ) {
			return $this->validateSingleColumnForm( $contentRows );
		}

		return false;
	}

	/**
	 * @param array[] $contentRows
	 * @param string[] $sideSelection
	 *
	 * @return bool
	 */
	private function validateSideSelection( array $contentRows, array $sideSelection ): bool {
		foreach ( $contentRows as $num => $row ) {
			$side = $sideSelection[$num] ?? 'copy';
			if ( !isset( $row[$side] ) || !is_string( $row[$side] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array[] $contentRows
	 *
	 * @return bool
	 */
	private function validateSingleColumnForm( array $contentRows ): bool {
		foreach ( $contentRows as $num => $row ) {
			if ( !is_array( $row ) || count( $row ) !== 1 ) {
				// Must be an array with exactly one column.
				return false;
			}
			$key = key( $row );
			if ( !in_array( $key, [ 'copy', 'other', 'your' ] ) ) {
				// Illegal key.
				return false;
			}
			if ( !is_string( $row[$key] ) ) {
				// Contents must be a plain string.
				return false;
			}
		}

		return true;
	}

}
