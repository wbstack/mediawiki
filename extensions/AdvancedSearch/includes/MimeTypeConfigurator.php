<?php

namespace AdvancedSearch;

use MimeAnalyzer;

/**
 * @license GPL-2.0-or-later
 */
class MimeTypeConfigurator {

	/**
	 * @var MimeAnalyzer
	 */
	private $mimeAnalyzer;

	/**
	 * @param MimeAnalyzer $mimeAnalyzer
	 */
	public function __construct( MimeAnalyzer $mimeAnalyzer ) {
		$this->mimeAnalyzer = $mimeAnalyzer;
	}

	/**
	 * @param string[] $fileExtensions
	 *
	 * @return string[] List of file extension => MIME type.
	 */
	public function getMimeTypes( array $fileExtensions ) {
		$mimeTypes = [];

		foreach ( $fileExtensions as $ext ) {
			$mimeType = $this->getFirstMimeTypeByFileExtension( $ext );
			if ( !isset( $mimeTypes[$mimeType] ) ) {
				$mimeTypes[$mimeType] = $ext;
			}
		}

		return array_flip( $mimeTypes );
	}

	/**
	 * Uses MimeAnalyzer to determine the mimetype of a given file extension
	 *
	 * @param string $fileExtension
	 * @return string First mime type associated with the given file extension
	 */
	private function getFirstMimeTypeByFileExtension( $fileExtension ) {
		return explode( ' ', $this->mimeAnalyzer->getTypesForExtension( $fileExtension ), 2 )[0];
	}

}
