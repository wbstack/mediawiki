<?php

namespace TwoColConflict\Logging;

/**
 * Provides a testable wrapper for MediaWiki core diff3 services.
 */
class ThreeWayMerge {

	/**
	 * Perform three-way merge and return an information structure.
	 * @param string $base
	 * @param string $other
	 * @param string $your
	 * @return ThreeWayMergeResult
	 */
	public function merge3( string $base, string $other, string $your ): ThreeWayMergeResult {
		$isCleanMerge = wfMerge(
			$base,
			$your,
			$other,
			$simplisticMergeAttempt,
			$mergeLeftovers );
		return new ThreeWayMergeResult( $isCleanMerge, $simplisticMergeAttempt, $mergeLeftovers );
	}

}
