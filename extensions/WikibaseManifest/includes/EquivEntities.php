<?php

namespace MediaWiki\Extension\WikibaseManifest;

use InvalidArgumentException;

class EquivEntities {

	/**
	 * @var array
	 */
	private $mapping;

	/**
	 * @param string[] $mapping
	 */
	public function __construct( array $mapping ) {
		$this->validateMapping( $mapping );
		$this->mapping = $mapping;
	}

	private function validateMapping( array $mapping ): void {
		foreach ( $mapping as $value ) {
			if ( !is_array( $value ) ) {
				throw new InvalidArgumentException(
					'Equivalent entities mappings should be grouped in arrays, e.g. "properties": [ "P31": "P1" ]'
				);
			}
			foreach ( $value as $remote => $local ) {
				if ( !is_string( $remote ) ) {
					throw new InvalidArgumentException( 'Keys of mapping should be strings' );
				}
				if ( !is_string( $local ) ) {
					throw new InvalidArgumentException( 'Values of mapping should be strings' );
				}
			}
		}
	}

	public function toArray(): array {
		return $this->mapping;
	}

}
