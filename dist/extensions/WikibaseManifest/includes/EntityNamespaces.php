<?php

namespace MediaWiki\Extension\WikibaseManifest;

use InvalidArgumentException;

class EntityNamespaces {

	public const NAMESPACE_ID = 'namespace_id';
	public const NAMESPACE_NAME = 'namespace_name';

	/**
	 * @var array
	 */
	private $mapping;

	/**
	 * @param array[] $mapping Key entity type, Value array keyed with namespaceId and
	 * namespaceString
	 */
	public function __construct( array $mapping ) {
		$this->validateMapping( $mapping );
		$this->mapping = $mapping;
	}

	private function validateMapping( array $mapping ): void {
		foreach ( $mapping as $k => $v ) {
			if ( !is_string( $k ) ) {
				throw new InvalidArgumentException( 'Keys of mapping should be strings' );
			}
			if (
				!is_array( $v )
				|| !array_key_exists( self::NAMESPACE_ID, $v )
				|| !array_key_exists( self::NAMESPACE_NAME, $v )
				|| !is_int( $v[self::NAMESPACE_ID] )
				|| !is_string( $v[self::NAMESPACE_NAME] )
			) {
				throw new InvalidArgumentException( 'Values of mapping should be arrays with needed keys and correct types' );
			}
		}
	}

	public function toArray(): array {
		return $this->mapping;
	}

}
