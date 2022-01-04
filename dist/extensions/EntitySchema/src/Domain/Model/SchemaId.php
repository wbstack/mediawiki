<?php

namespace EntitySchema\Domain\Model;

use InvalidArgumentException;

/**
 * @license GPL-2.0-or-later
 */
class SchemaId {

	/** @var string */
	private $id;

	private const PATTERN = '/^E[1-9][0-9]*\z/';

	/**
	 * @param string $id
	 */
	public function __construct( $id ) {
		if ( !preg_match( self::PATTERN, $id ) ) {
			throw new InvalidArgumentException( 'ID must match ' . self::PATTERN );
		}

		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

}
