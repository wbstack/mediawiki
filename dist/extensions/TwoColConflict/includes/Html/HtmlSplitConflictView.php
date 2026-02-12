<?php

namespace TwoColConflict\Html;

use MediaWiki\Html\Html;
use MessageLocalizer;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSplitConflictView {

	private HtmlEditableTextComponent $editableTextComponent;
	private MessageLocalizer $messageLocalizer;

	public function __construct(
		HtmlEditableTextComponent $editableTextComponent,
		MessageLocalizer $messageLocalizer
	) {
		$this->editableTextComponent = $editableTextComponent;
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @param array[] $unifiedDiff
	 * @param bool $markAllAsIncomplete
	 *
	 * @return string HTML
	 */
	public function getHtml(
		array $unifiedDiff,
		bool $markAllAsIncomplete
	): string {
		$out = '';

		foreach ( $unifiedDiff as $i => $changeSet ) {
			if ( $changeSet['action'] === 'copy' ) {
				// Copy block across both columns.
				$line = $this->buildCopiedLine( $changeSet['copytext'], $i );
				$markAsIncomplete = false;
			} else {
				// Old and new split across two columns.
				$line = $this->buildRemovedLine(
						$changeSet['oldhtml'],
						$changeSet['oldtext'],
						$i
					) .
					( new HtmlSideSelectorComponent( $this->messageLocalizer ) )
						->getRowHtml( $i ) .
					$this->buildAddedLine(
						$changeSet['newhtml'],
						$changeSet['newtext'],
						$i
					);
				$markAsIncomplete = $markAllAsIncomplete;
			}

			$out .= Html::rawElement(
				'div',
				[
					'class' => 'mw-twocolconflict-split-row' .
						( $markAsIncomplete ? ' mw-twocolconflict-no-selection' : '' ),
				],
				$line
			);
		}

		return Html::rawElement( 'div', [ 'class' => 'mw-twocolconflict-split-view' ], $out );
	}

	private function buildAddedLine( string $diffHtml, ?string $text, int $rowNum ): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-add mw-twocolconflict-split-column' ],
			$this->editableTextComponent->getHtml( $diffHtml, $text, $rowNum, 'your' )
		);
	}

	private function buildRemovedLine( string $diffHtml, ?string $rawText, int $rowNum ): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-delete mw-twocolconflict-split-column' ],
			$this->editableTextComponent->getHtml( $diffHtml, $rawText, $rowNum, 'other' )
		);
	}

	private function buildCopiedLine( string $text, int $rowNum ): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-split-column' ],
			$this->editableTextComponent->getHtml( htmlspecialchars( $text ), $text, $rowNum, 'copy' )
		);
	}

}
