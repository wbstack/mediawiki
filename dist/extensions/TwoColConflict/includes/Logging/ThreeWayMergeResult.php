<?php

namespace TwoColConflict\Logging;

/**
 * @license GPL-2.0-or-later
 */
class ThreeWayMergeResult {

	/** @var bool True when the merge is safe to perform automatically. */
	private $isCleanMerge;
	/** @var string Result of automatic merge, with overlapping chunks defaulting to "your" text. */
	private $simplisticMergeAttempt;
	/** @var string "sed" script which would overwrite your text with the overlapping "other" text. */
	private $mergeLeftovers;
	/** @var string Regex to divide a diff3-produced sed script into individual chunks. */
	private const SED_UNIT_PARSER =
		'/
			(?<=^|\n)             # Anchor at the beginning of the document or after a newline.
			(                     # Commands with vs. without text are treated separately.
				(?<cmd>           # Capture the sed specification for this chunk.
					[0-9]+        # Start line number
					(?:,[0-9]+)?  # Optional end line number
					(?:a|c)       # Both add and change chunks
				)\n
				(?<text>
					.*?           # Capture new text.
				)\n
				\.\n              # Ending "." on its own line.
			|
				(?<cmd>
					[0-9]+
					(?:,[0-9]+)?
					d             # A deletion command has no text.
				)\n
			)
		/Jsx';

	/**
	 * @param bool $isCleanMerge
	 * @param string $simplisticMergeAttempt
	 * @param string $mergeLeftovers
	 */
	public function __construct(
		bool $isCleanMerge,
		string $simplisticMergeAttempt,
		string $mergeLeftovers
	) {
		$this->isCleanMerge = $isCleanMerge;
		$this->simplisticMergeAttempt = $simplisticMergeAttempt;
		$this->mergeLeftovers = $mergeLeftovers;
	}

	/**
	 * @return bool True if the automatic merge succeeded.
	 */
	public function isCleanMerge(): bool {
		return $this->isCleanMerge;
	}

	/**
	 * @return int Number of chunks which could not be automatically merged
	 */
	public function getOverlappingChunkCount(): int {
		return count( $this->getOverlappingChunks() );
	}

	/**
	 * Count the amount of text in the "other" side of each unresolvable chunk.
	 *
	 * @return int Total number of characters in the chunks which could not be automatically merged.
	 */
	public function getOverlappingChunkSize(): int {
		$totalChars = 0;
		foreach ( $this->getOverlappingChunks() as $chunk ) {
			$totalChars += mb_strlen( $chunk['text'] );
		}
		return $totalChars;
	}

	private function getOverlappingChunks(): array {
		// TODO: This function is rumored to segfault at about 80kB of haystack.
		preg_match_all(
			self::SED_UNIT_PARSER,
			$this->mergeLeftovers,
			$matches,
			PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL );
		return $matches;
	}

}
