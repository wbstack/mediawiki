<?php

namespace TwoColConflict;

use Wikimedia\Diff\ComplexityException;
use Wikimedia\Diff\Diff;
use Wikimedia\Diff\WordAccumulator;
use Wikimedia\Diff\WordLevelDiff;

/**
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class AnnotatedHtmlDiffFormatter {

	/**
	 * @param string[] $oldLines
	 * @param string[] $newLines
	 * @param string[] $preSaveTransformedLines
	 *
	 * @throws ComplexityException
	 * @return array[] List of changes, each of which include an HTML representation of the diff,
	 *  and the original wikitext. Note the HTML does not use <br> but relies on `white-space:
	 *  pre-line` being set!
	 * TODO: "preSavedTransformedLines" is still warty.
	 */
	public function format(
		array $oldLines,
		array $newLines,
		array $preSaveTransformedLines
	): array {
		$changes = [];
		$oldLine = 0;
		$newLine = 0;
		$diff = new Diff( $oldLines, $preSaveTransformedLines );

		foreach ( $diff->getEdits() as $edit ) {
			switch ( $edit->getType() ) {
				case 'add':
					$changes[] = [
						'action' => 'add',
						'oldhtml' => "\u{00A0}",
						'oldtext' => null,
						'newhtml' => '<ins class="mw-twocolconflict-diffchange">' .
							$this->composeHtml( $edit->getClosing() ) . '</ins>',
						'newtext' => implode( "\n",
							array_slice( $newLines, $newLine, $edit->nclosing() ) ),
					];
					break;

				case 'delete':
					$changes[] = [
						'action' => 'delete',
						'oldhtml' => '<del class="mw-twocolconflict-diffchange">' .
							$this->composeHtml( $edit->getOrig() ) . '</del>',
						'oldtext' => implode( "\n", $edit->getOrig() ),
						'newhtml' => "\u{00A0}",
						'newtext' => null,
					];
					break;

				case 'change':
					$wordLevelDiff = $this->rTrimmedWordLevelDiff( $edit->getOrig(), $edit->getClosing() );
					$changes[] = [
						'action' => 'change',
						'oldhtml' => $this->getOriginalInlineDiff( $wordLevelDiff ),
						'oldtext' => implode( "\n", $edit->getOrig() ),
						'newhtml' => $this->getClosingInlineDiff( $wordLevelDiff ),
						'newtext' => implode( "\n",
							array_slice( $newLines, $newLine, $edit->nclosing() ) ),
					];
					break;

				case 'copy':
					$changes[] = [
						'action' => 'copy',
						// Warning, this must be unescaped Wikitext, not escaped HTML!
						'copytext' => implode( "\n", $edit->getOrig() ),
					];
					break;
			}

			$oldLine += $edit->norig();
			$newLine += $edit->nclosing();
		}

		// Try to merge unchanged newline-only rows into a more meaningful row
		foreach ( $changes as $i => $row ) {
			if ( !isset( $row['copytext'] ) || trim( $row['copytext'], "\n" ) !== '' ) {
				continue;
			}

			// Prefer adding extra empty lines to the end of the previous row
			foreach ( [ -1, 1 ] as $offset ) {
				if ( !isset( $changes[$i + $offset] ) ) {
					continue;
				}

				$target = &$changes[$i + $offset];
				if ( isset( $target['oldtext'] ) && isset( $target['newtext'] ) ) {
					$extra = "\n" . $row['copytext'];
					if ( $offset < 0 ) {
						$target['oldtext'] .= $extra;
						$target['newtext'] .= $extra;
					} else {
						$target['oldtext'] = $extra . $target['oldtext'];
						$target['newtext'] = $extra . $target['newtext'];
					}
					unset( $changes[$i] );
					break;
				}
			}
		}

		return array_values( $changes );
	}

	/**
	 * @param string[] $before
	 * @param string[] $after
	 *
	 * @return WordLevelDiff
	 */
	private function rTrimmedWordLevelDiff( array $before, array $after ): WordLevelDiff {
		end( $before );
		end( $after );
		$this->commonRTrim( $before[key( $before )], $after[key( $after )] );
		return new WordLevelDiff( $before, $after );
	}

	/**
	 * Trims identical sequences of whitespace from the end of both lines.
	 *
	 * @param string &$before
	 * @param string &$after
	 */
	private function commonRTrim( string &$before, string &$after ): void {
		$uncommonBefore = strlen( $before );
		$uncommonAfter = strlen( $after );
		while ( $uncommonBefore > 0 &&
			$uncommonAfter > 0 &&
			$before[$uncommonBefore - 1] === $after[$uncommonAfter - 1] &&
			ctype_space( $after[$uncommonAfter - 1] )
		) {
			$uncommonBefore--;
			$uncommonAfter--;
		}
		$before = substr( $before, 0, $uncommonBefore );
		$after = substr( $after, 0, $uncommonAfter );
	}

	/**
	 * Composes lines from a WordLevelDiff and marks removed words.
	 *
	 * @param WordLevelDiff $diff
	 *
	 * @return string Composed HTML string with inline markup
	 */
	private function getOriginalInlineDiff( WordLevelDiff $diff ): string {
		$wordAccumulator = $this->getWordAccumulator();

		foreach ( $diff->getEdits() as $edit ) {
			if ( $edit->type === 'copy' ) {
				$wordAccumulator->addWords( $edit->orig );
			} elseif ( $edit->orig ) {
				$wordAccumulator->addWords( $edit->orig, 'del' );
			}
		}
		return implode( "\n", $wordAccumulator->getLines() );
	}

	/**
	 * Composes lines from a WordLevelDiff and marks added words.
	 *
	 * @param WordLevelDiff $diff
	 *
	 * @return string Composed HTML string with inline markup
	 */
	private function getClosingInlineDiff( WordLevelDiff $diff ): string {
		$wordAccumulator = $this->getWordAccumulator();

		foreach ( $diff->getEdits() as $edit ) {
			if ( $edit->type === 'copy' ) {
				$wordAccumulator->addWords( $edit->closing );
			} elseif ( $edit->closing ) {
				$wordAccumulator->addWords( $edit->closing, 'ins' );
			}
		}
		return implode( "\n", $wordAccumulator->getLines() );
	}

	/**
	 * @return WordAccumulator
	 */
	private function getWordAccumulator(): WordAccumulator {
		$wordAccumulator = new WordAccumulator();
		$wordAccumulator->insClass = ' class="mw-twocolconflict-diffchange"';
		$wordAccumulator->delClass = ' class="mw-twocolconflict-diffchange"';
		return $wordAccumulator;
	}

	/**
	 * @param string[] $lines
	 *
	 * @return string HTML without <br>, relying on `white-space: pre-line` being set
	 */
	private function composeHtml( array $lines ): string {
		return htmlspecialchars( implode( "\n", array_map(
			static function ( string $line ): string {
				// Replace empty lines with a non-breaking space
				return $line === '' ? "\u{00A0}" : $line;
			},
			$lines
		) ) );
	}

}
