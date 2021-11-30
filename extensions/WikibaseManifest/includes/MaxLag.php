<?php

namespace MediaWiki\Extension\WikibaseManifest;

use InvalidArgumentException;

class MaxLag {

	/**
	 * @var mixed the max_lag value
	 */
	private $value;

	/**
	 * @param mixed $value
	 */
	public function __construct( $value ) {
		$this->validateValue( $value );
		$this->value = $value;
	}

	private function validateValue( $value ): void {
		if ( !is_int( $value ) ) {
			throw new InvalidArgumentException( 'The max_lag value must be an integer number.' );
		}
	}

	public function getValue(): int {
		return $this->value;
	}
}
