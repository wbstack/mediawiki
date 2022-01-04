<?php

namespace Wikibase\Lib\Store;

use Wikibase\DataModel\Entity\EntityId;
use Wikimedia\Assert\Assert;

/**
 * Represents result of `\Wikibase\Lib\Store\EntityRevisionLookup::getLatestRevisionId` method call.
 * Is immutable.
 *
 * The idea behind the design is to force developer to handle all the possible cases.
 *
 * How to create:
 * ```php
 * $concreteRevision = LatestRevisionIdResult::concreteRevision( 123 );
 * $revisionRedirectsToQ7 = LatestRevisionIdResult::redirect( 123, new ItemId( 'Q7' ) );
 * $entityDoesNotExist = LatestRevisionIdResult::nonexistentEntity();
 * ```
 *
 * Example of usage:
 * ```php
 * 	$result = $someResult->onRedirect( function ( int $revisionId, EntityId $redirectsTo ) {
 * 			return 'redirect';
 * 		} )
 * 		->onNonexistentEntity( function () {
 * 			return 'nonexistent';
 * 		} )
 * 		->onConcreteRevision( function ( int $revisionId ) {
 * 			return 'concrete';
 * 		} )
 * 		->map();
 *
 * 	// $result will be one of 'redirect', 'nonexistent' or 'concrete' depending
 * 	// on the $someResult type
 * ```
 *
 * @see \Wikibase\Lib\Store\EntityRevisionLookup::getLatestRevisionId
 * @license GPL-2.0-or-later
 */
final class LatestRevisionIdResult {

	/**
	 * Constants to specify type of the result
	 */
	private const REDIRECT = 'redirect';
	private const NONEXISTENT = 'nonexistent';
	private const CONCRETE_REVISION = 'concrete revision';

	/**
	 * @var string One of the constants
	 */
	private $type;

	/**
	 * @var callable[] Indexed by type. See constants
	 */
	private $handlers = [];

	/**
	 * @var int|null Revision id if present
	 */
	private $revisionId;

	/**
	 * @var EntityId|null
	 */
	private $redirectsTo;

	/**
	 * @param int $revisionId
	 * @param EntityId $redirectsTo (could be another redirect)
	 * @return self
	 */
	public static function redirect( $revisionId, EntityId $redirectsTo ) {
		self::assertCorrectRevisionId( $revisionId );

		$result = new self( self::REDIRECT );
		$result->revisionId = $revisionId;
		$result->redirectsTo = $redirectsTo;

		return $result;
	}

	/**
	 * @return self
	 */
	public static function nonexistentEntity() {
		return new self( self::NONEXISTENT );
	}

	/**
	 * @param int $revisionId
	 * @return self
	 */
	public static function concreteRevision( $revisionId ) {
		self::assertCorrectRevisionId( $revisionId );

		$result = new self( self::CONCRETE_REVISION );
		$result->revisionId = $revisionId;
		return $result;
	}

	private function __construct( $type ) {
		$this->type = $type;
	}

	/**
	 * @param callable $handler Revision id will be given as a first argument
	 * @return self
	 */
	public function onConcreteRevision( callable $handler ) {
		$result = clone $this;
		$result->handlers[ self::CONCRETE_REVISION ] = $handler;

		return $result;
	}

	/**
	 * @param callable $handler Revision id will be given as a first argument, EntityId to which
	 * 							revision redirects will be second argument
	 * @return self
	 */
	public function onRedirect( callable $handler ) {
		$result = clone $this;
		$result->handlers[ self::REDIRECT] = $handler;

		return $result;
	}

	/**
	 * @param callable $handler Function with no arguments
	 * @return self
	 */
	public function onNonexistentEntity( callable $handler ) {
		$result = clone $this;
		$result->handlers[ self::NONEXISTENT ] = $handler;

		return $result;
	}

	/**
	 * @return mixed Returns value returned by one of the map functions
	 * @throws \Exception If target handler throws an exception
	 * @throws \LogicException If not all the handlers are specified
	 */
	public function map() {
		if ( count( $this->handlers ) !== 3 ) {
			throw new \LogicException( 'Not all handlers are provided' );
		}

		$targetHandler = $this->handlers[ $this->type ];
		switch ( $this->type ) {
			case self::NONEXISTENT:
				return $targetHandler();
			case self::REDIRECT:
				return $targetHandler( $this->revisionId, $this->redirectsTo );
			case self::CONCRETE_REVISION:
				return $targetHandler( $this->revisionId );
			default:
				throw new \RuntimeException( 'Unreachable' );
		}
	}

	/**
	 * @param mixed $revisionId Expected positive integer
	 * @throws \Exception
	 */
	private static function assertCorrectRevisionId( $revisionId ) {
		Assert::parameterType( 'integer', $revisionId, '$revisionId' );
		Assert::parameter(
			$revisionId > 0,
			'$revisionId',
			'Should be greater than zero'
		);
	}

}
