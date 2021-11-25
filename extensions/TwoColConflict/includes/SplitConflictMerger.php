<?php

namespace TwoColConflict;

/**
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class SplitConflictMerger {

	/**
	 * @param array[] $contentRows
	 * @param array[] $extraLineFeeds
	 * @param string[]|string $sideSelection Either an array of side identifiers per row ("copy",
	 *  "other", or "your"). Or one side identifier for all rows (either "other" or "your").
	 *
	 * @return string Wikitext
	 */
	public function mergeSplitConflictResults(
		array $contentRows,
		array $extraLineFeeds,
		$sideSelection
	): string {
		$textLines = [];

		foreach ( $contentRows as $num => $row ) {
			if ( is_array( $sideSelection ) ) {
				// There was no selection to be made for "copy" rows in the interface
				$side = $sideSelection[$num] ?? 'copy';
			} else {
				$side = isset( $row['copy'] ) ? 'copy' : $sideSelection;
			}

			$line = $this->pickBestPossibleValue( $row, $side );
			// Don't remove all whitespace, because this is not necessarily the end of the article
			$line = rtrim( $line, "\r\n" );
			// *Possibly* emptied by the user, or the line was empty before
			$emptiedByUser = $line === '';

			if ( isset( $extraLineFeeds[$num] ) ) {
				$value = $this->pickBestPossibleValue( $extraLineFeeds[$num], $side );
				[ $before, $after ] = $this->parseExtraLineFeeds( $value );
				// We want to understand the difference between a row the user emptied (extra
				// linefeeds are removed as well then), or a row that was empty before. This is
				// how HtmlEditableTextComponent marked empty rows.
				if ( $before === 'was-empty' ) {
					$emptiedByUser = false;
				} else {
					$line = $this->lineFeeds( $before ) . $line;
				}
				$line .= $this->lineFeeds( $after );
			}

			// In case a line was emptied, we need to skip the extra linefeeds as well
			if ( !$emptiedByUser ) {
				$textLines[] = $line;
			}
		}
		return SplitConflictUtils::mergeTextLines( $textLines );
	}

	/**
	 * @param string[]|mixed $postedValues Typically an array of strings, but not guaranteed
	 * @param string $key Preferred array key to pick from the list of values, if present
	 *
	 * @return string
	 */
	private function pickBestPossibleValue( $postedValues, string $key ): string {
		// A mismatch here means the request is either incomplete (by design) or broken, and already
		// detected as such (see ConflictFormValidator). Intentionally return the most recent, most
		// conflicting value. Fall back to the users unsaved edit, or to *whatever* is there, no
		// matter how invalid it might be. We *never* want to loose anything.
		return (string)(
			$postedValues[$key] ??
			$postedValues['your'] ??
			current( (array)$postedValues )
		);
	}

	/**
	 * @param string $postedValue
	 *
	 * @return string[]
	 */
	private function parseExtraLineFeeds( string $postedValue ): array {
		$counts = explode( ',', $postedValue, 2 );
		// "Before" and "after" are intentionally flipped, because "before" is very rare
		return [ $counts[1] ?? '', $counts[0] ];
	}

	private function lineFeeds( string $count ): string {
		$count = (int)$count;

		// Arbitrary limit just to not end with megabytes in case of an attack
		if ( $count <= 0 || $count > 1000 ) {
			return '';
		}

		return str_repeat( "\n", $count );
	}

}
