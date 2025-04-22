<?php

namespace TwoColConflict\TalkPageConflict;

use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use TwoColConflict\AnnotatedHtmlDiffFormatter;
use TwoColConflict\SplitConflictUtils;
use Wikimedia\Diff\ComplexityException;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class ResolutionSuggester {

	private ?RevisionRecord $baseRevision;
	private string $contentFormat;

	public function __construct( ?RevisionRecord $baseRevision, string $contentFormat ) {
		$this->baseRevision = $baseRevision;
		$this->contentFormat = $contentFormat;
	}

	/**
	 * @param array $a First block to compare
	 * @param array $b Second block
	 * @return bool True if the blocks are both copy blocks, with identical content.
	 */
	private function isIdenticalCopyBlock( array $a, array $b ): bool {
		return $a['action'] === 'copy' && $a === $b;
	}

	private function isAddition( array $change ): bool {
		return $change['action'] === 'add'
			|| ( $change['action'] === 'change' && $change['oldtext'] === '' );
	}

	/**
	 * @param string[] $storedLines
	 * @param string[] $yourLines
	 * @return TalkPageResolution|null
	 */
	public function getResolutionSuggestion(
		array $storedLines,
		array $yourLines
	): ?TalkPageResolution {
		$baseLines = $this->getBaseRevisionLines();
		$formatter = new AnnotatedHtmlDiffFormatter();

		try {
			// TODO: preSaveTransform $yourLines, but not $storedLines
			$diffYourLines = $formatter->format( $baseLines, $yourLines, $yourLines );
			$diffStoredLines = $formatter->format( $baseLines, $storedLines, $storedLines );
		} catch ( ComplexityException $ex ) {
			return null;
		}

		if ( count( $diffYourLines ) !== count( $diffStoredLines ) ) {
			return null;
		}

		foreach ( $diffStoredLines as $i => $stored ) {
			$unsaved = $diffYourLines[$i];

			// We only care about copies that are *almost* identical, except for extra newlines
			if ( !isset( $stored['copytext'] )
				|| !isset( $unsaved['copytext'] )
				|| $stored['copytext'] === $unsaved['copytext']
				|| trim( $stored['copytext'], "\n" ) !== trim( $unsaved['copytext'], "\n" )
			) {
				continue;
			}

			[ $beforeStored, $afterStored ] = $this->countNewlines( $stored['copytext'] );
			[ $beforeUnsafed, $afterUnsafed ] = $this->countNewlines( $unsaved['copytext'] );

			$this->moveNewlinesUp( $diffStoredLines, $i, $beforeStored - $beforeUnsafed );
			$this->moveNewlinesUp( $diffYourLines, $i, $beforeUnsafed - $beforeStored );
			$this->moveNewlinesDown( $diffStoredLines, $i, $afterStored - $afterUnsafed );
			$this->moveNewlinesDown( $diffYourLines, $i, $afterUnsafed - $afterStored );
		}

		// only diffs that contain exactly one addition, that is optionally
		// preceded and/or succeeded by one identical copy line, are
		// candidates for the resolution suggestion

		$diff = [];
		/** @var ?int $spliceIndex */
		$spliceIndex = null;
		// Copy over identical blocks, and splice the two alternatives.
		foreach ( $diffYourLines as $index => $yourLine ) {
			$otherLine = $diffStoredLines[$index];
			if ( $this->isIdenticalCopyBlock( $yourLine, $otherLine ) ) {
				// Copy
				$diff[] = $otherLine;
			} elseif ( $this->isAddition( $yourLine )
				&& $this->isAddition( $otherLine )
				&& $spliceIndex === null
			) {
				// Splice alternatives
				$spliceIndex = count( $diff );
				$diff[] = [ 'action' => 'add' ] + $otherLine;
				$diff[] = [ 'action' => 'add' ] + $yourLine;
			} else {
				return null;
			}
		}
		if ( $spliceIndex === null ) {
			// TODO: I'm not sure yet, but this might be a logic error and should be logged.
			return null;
		}

		// @phan-suppress-next-line SecurityCheck-DoubleEscaped
		return new TalkPageResolution( $diff, $spliceIndex, $spliceIndex + 1 );
	}

	/**
	 * @return string[]
	 */
	private function getBaseRevisionLines(): array {
		if ( !$this->baseRevision ) {
			return [];
		}

		$baseContent = $this->baseRevision->getContent( SlotRecord::MAIN );
		if ( !$baseContent ) {
			return [];
		}

		$baseText = $baseContent->serialize( $this->contentFormat );
		if ( !$baseText ) {
			return [];
		}

		return SplitConflictUtils::splitText( $baseText );
	}

	private function countNewlines( string $text ): array {
		// Start from the end, because we want "\n" to be reported as [ 0, 1 ]
		$endOfText = strlen( rtrim( $text, "\n" ) );
		$after = strlen( $text ) - $endOfText;
		$before = strspn( $text, "\n", 0, $endOfText );
		return [ $before, $after ];
	}

	private function moveNewlinesUp( array &$diff, int $i, int $count ): void {
		if ( $count < 1 || !isset( $diff[$i - 1] ) || $diff[$i - 1]['action'] !== 'add' ) {
			return;
		}

		$diff[$i - 1]['newtext'] .= str_repeat( "\n", $count );
		// The current row is guaranteed to be a copy
		$diff[$i]['copytext'] = substr( $diff[$i]['copytext'], $count );
	}

	private function moveNewlinesDown( array &$diff, int $i, int $count ): void {
		if ( $count < 1 || !isset( $diff[$i + 1] ) || $diff[$i + 1]['action'] !== 'add' ) {
			return;
		}

		$diff[$i + 1]['newtext'] = str_repeat( "\n", $count ) . $diff[$i + 1]['newtext'];
		// The current row is guaranteed to be a copy
		$diff[$i]['copytext'] = substr( $diff[$i]['copytext'], 0, -$count );
	}

}
