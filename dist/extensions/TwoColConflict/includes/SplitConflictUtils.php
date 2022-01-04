<?php

namespace TwoColConflict;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SplitConflictUtils {

	/**
	 * @param string $text
	 *
	 * @return string[]
	 */
	public static function splitText( string $text ): array {
		return explode( "\n", str_replace( [ "\r\n", "\r" ], "\n", $text ) );
	}

	/**
	 * @param string[] $textLines
	 *
	 * @return string
	 */
	public static function mergeTextLines( array $textLines ): string {
		return str_replace( [ "\r\n", "\r" ], "\n", implode( "\r\n", $textLines ) );
	}

	/**
	 * @param string $html
	 *
	 * @return string
	 */
	public static function addTargetBlankToLinks( string $html ): string {
		return preg_replace( '/<a\b(?![^<>]*\starget=)/', '<a target="_blank"', $html );
	}

}
