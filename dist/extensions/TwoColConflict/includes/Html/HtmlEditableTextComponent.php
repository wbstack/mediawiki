<?php

namespace TwoColConflict\Html;

use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MessageLocalizer;
use OOUI\ButtonWidget;

/**
 * @license GPL-2.0-or-later
 */
class HtmlEditableTextComponent {

	private MessageLocalizer $messageLocalizer;
	private Language $language;
	private ?string $editFontOption;

	/**
	 * @param MessageLocalizer $messageLocalizer
	 * @param Language $language
	 * @param string|null $editFontOption Supported values are "monospace" (default), "sans-serif",
	 *  and "serif"
	 */
	public function __construct(
		MessageLocalizer $messageLocalizer,
		Language $language,
		?string $editFontOption = null
	) {
		$this->messageLocalizer = $messageLocalizer;
		$this->language = $language;
		$this->editFontOption = $editFontOption;
	}

	/**
	 * @param string $diffHtml
	 * @param string|null $text
	 * @param int $rowNum
	 * @param string $changeType
	 * @param bool $isDisabled
	 *
	 * @return string
	 */
	public function getHtml(
		string $diffHtml,
		?string $text,
		int $rowNum,
		string $changeType,
		bool $isDisabled = false
	): string {
		$diffHtml = trim( $diffHtml, "\r\n\u{00A0}" );
		$editorText = trim( (string)$text, "\r\n" );
		// This duplicates what \MediaWiki\EditPage\EditPage::addNewLineAtEnd() does
		if ( $editorText !== '' ) {
			$editorText .= "\n";
		}
		// FIXME: Remove the temporary "notheme", see T373231
		$classes = [ 'mw-twocolconflict-split-editable', 'notheme' ];

		$innerHtml = Html::rawElement(
			'span',
			[ 'class' => 'mw-twocolconflict-split-difftext' ],
			$diffHtml
		);
		$innerHtml .= Html::element( 'div', [ 'class' => 'mw-twocolconflict-split-fade' ] );
		$innerHtml .= $this->buildTextEditor( $editorText, $rowNum, $changeType, $isDisabled );
		if ( !$isDisabled ) {
			$innerHtml .= $this->buildEditButton();
			$innerHtml .= $this->buildSaveButton();
			$innerHtml .= $this->buildResetButton();
		}

		if ( $changeType === 'copy' ) {
			$innerHtml .= $this->buildExpandButton();
			$innerHtml .= $this->buildCollapseButton();
			$classes[] = 'mw-twocolconflict-split-collapsed';
		}

		$innerHtml .= $this->buildResetElements( $diffHtml );
		$innerHtml .= $this->buildLineFeedField( $text, $rowNum, $changeType );

		return Html::rawElement( 'div', [ 'class' => $classes ], $innerHtml );
	}

	private function buildResetElements( string $diffHtml ): string {
		return Html::rawElement(
				'span', [ 'class' => 'mw-twocolconflict-split-reset-diff-text' ],
				$diffHtml
			);
	}

	private function buildTextEditor(
		string $editorText,
		int $rowNum,
		string $changeType,
		bool $isDisabled
	): string {
		$attributes = [
			'class' => 'mw-editfont-' . ( $this->editFontOption ?: 'monospace' ) . ' mw-twocolconflict-split-editor',
			'lang' => $this->language->getHtmlCode(),
			'dir' => $this->language->getDir(),
			'rows' => $this->rowsForText( $editorText ),
			'autocomplete' => 'off',
			'tabindex' => '1',
		];
		if ( $isDisabled ) {
			$attributes[] = 'readonly';
		}

		/**
		 * "If the next token is a U+000A LINE FEED (LF) character token, then ignore that token and
		 * move on to the next one. (Newlines at the start of textarea elements are ignored as an
		 * authoring convenience.)"
		 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inbody
		 * Html::textarea() respects this, but Html::element() doesn't.
		 */
		return Html::textarea(
			'mw-twocolconflict-split-content[' . $rowNum . '][' . $changeType . ']',
			$editorText,
			$attributes
		);
	}

	private function buildLineFeedField( ?string $text, int $rowNum, string $changeType ): string {
		$counts = $this->countExtraLineFeeds( $text );

		if ( $counts === '0' ) {
			// Reduce the stuff we transfer back and forth if it's the default value anyway
			return '';
		}

		return Html::hidden( "mw-twocolconflict-split-linefeeds[$rowNum][$changeType]", $counts );
	}

	private function buildEditButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'edit',
			'title' => $this->messageLocalizer->msg( 'twocolconflict-split-edit-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-edit-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildSaveButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'check',
			'title' => $this->messageLocalizer->msg( 'twocolconflict-split-save-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-save-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildResetButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'close',
			'title' => $this->messageLocalizer->msg( 'twocolconflict-split-reset-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-reset-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildExpandButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'expand',
			'title' => $this->messageLocalizer->msg( 'twocolconflict-split-expand-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-expand-button' ],
			'tabIndex' => '1',
		] );
	}

	private function buildCollapseButton() {
		return new ButtonWidget( [
			'infusable' => true,
			'framed' => false,
			'icon' => 'collapse',
			'title' => $this->messageLocalizer->msg( 'twocolconflict-split-collapse-tooltip' )->text(),
			'classes' => [ 'mw-twocolconflict-split-collapse-button' ],
			'tabIndex' => '1',
		] );
	}

	private function countExtraLineFeeds( ?string $text ): string {
		if ( $text === null ) {
			return '0';
		}

		$endOfText = strlen( rtrim( $text, "\r\n" ) );
		$after = substr_count( $text, "\n", $endOfText );

		// Detect text that contains nothing but linebreaks, i.e. appears empty
		if ( $endOfText === 0 ) {
			// The merger will drop sections that have been emptied by the user, except they are
			// marked as "was empty before".
			return "$after,was-empty";
		}

		$before = substr_count( $text, "\n", 0, strspn( $text, "\r\n", 0, $endOfText ) );
		if ( $before ) {
			// "Before" and "after" are intentionally flipped, because "before" is very rare
			return "$after,$before";
		} else {
			return (string)$after;
		}
	}

	/**
	 * Estimate the appropriate size textbox to use for a given text.
	 *
	 * @param string $text Contents of the textbox
	 *
	 * @return int Suggested number of rows
	 */
	private function rowsForText( string $text ): int {
		$thresholds = [
			80 * 10 => 18,
			80 * 4 => 6,
			0 => 3,
		];
		$numChars = function_exists( 'grapheme_strlen' )
			? grapheme_strlen( $text ) : mb_strlen( $text );
		$upperLimit = min( substr_count( $text, "\n" ) + 1, 2 * 18 );
		foreach ( $thresholds as $minChars => $rows ) {
			if ( $numChars >= $minChars ) {
				return max( $rows, $upperLimit );
			}
		}
		// Should be unreachable.
		return $upperLimit;
	}

}
