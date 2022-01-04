<?php

namespace Wikibase\Lexeme\Domain\Diff;

use Diff\DiffOp\Diff\Diff;
use LogicException;
use Wikibase\Lexeme\Domain\Model\Sense;

/**
 * @license GPL-2.0-or-later
 * @phan-file-suppress PhanPluginNeverReturnMethod
 */
class AddSenseDiff implements SenseDiff {

	/**
	 * @var Sense
	 */
	private $sense;

	/**
	 * @var Diff
	 */
	private $diffOps;

	public function __construct( Sense $addedSense, Diff $diffOps ) {
		$this->sense = $addedSense;
		$this->diffOps = $diffOps;
	}

	public function getAddedSense() {
		return $this->sense;
	}

	/**
	 * @return Diff
	 */
	public function getGlossesDiff() {
		return $this->diffOps['glosses'] ?? new Diff( [] );
	}

	/**
	 * @return Diff
	 */
	public function getStatementsDiff() {
		return $this->diffOps['claim'] ?? new Diff( [] );
	}

	public function serialize() {
		throw new LogicException( "serialize() is not implemented" );
	}

	public function unserialize( $serialized ) {
		throw new LogicException( "unserialize() is not implemented" );
	}

	public function getType(): string {
		return 'diff';
	}

	public function isAtomic(): bool {
		return false;
	}

	public function toArray( callable $valueConverter = null ): array {
		throw new LogicException( 'toArray() is not implemented' );
	}

	public function count() {
		return $this->diffOps->count();
	}

	public function getOperations() {
		// Due to the way this DiffOp is structured the default implementation would return nothing
		throw new LogicException( "getOperations() is not implemented" );
	}

	public function getArrayCopy() {
		// Due to the way this DiffOp is structured the default implementation would return nothing
		throw new LogicException( "getArrayCopy() is not implemented" );
	}

}
