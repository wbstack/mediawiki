<?php

namespace TwoColConflict\TalkPageConflict;

/**
 * Container for talk page use case resolution.  This is populated by the suggester, and can be
 * manipulated by editing your text or reordering your and the other text block.
 *
 * @codeCoverageIgnore Trivial, even immutable value object
 *
 * @license GPL-2.0-or-later
 */
class TalkPageResolution {

	/** @var array[] */
	private array $diff;
	private int $otherIndex;
	private int $yourIndex;

	/**
	 * @param array[] $diff A list of changes as created by the AnnotatedHtmlDiffFormatter
	 * @param int $otherIndex
	 * @param int $yourIndex
	 */
	public function __construct( array $diff, int $otherIndex, int $yourIndex ) {
		$this->diff = $diff;
		$this->otherIndex = $otherIndex;
		$this->yourIndex = $yourIndex;
	}

	/**
	 * @return array[]
	 */
	public function getDiff(): array {
		return $this->diff;
	}

	public function getOtherIndex(): int {
		return $this->otherIndex;
	}

	public function getYourIndex(): int {
		return $this->yourIndex;
	}

}
