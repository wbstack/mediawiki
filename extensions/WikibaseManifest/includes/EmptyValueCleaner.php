<?php

namespace MediaWiki\Extension\WikibaseManifest;

class EmptyValueCleaner {

	public function omitEmptyValues( $array ) {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$array[ $key ] = $this->omitEmptyValues( $array[ $key ] );
			}
			if ( $array[ $key ] === '' || $array[ $key ] === [] || $array[ $key ] === null ) {
				unset( $array[ $key ] );
			}
		}
		return $array;
	}
}
