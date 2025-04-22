<?php

namespace TwoColConflict\Html;

use MediaWiki\Html\Html;
use MessageLocalizer;
use OOUI\RadioInputWidget;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSideSelectorComponent {

	private MessageLocalizer $messageLocalizer;

	public function __construct( MessageLocalizer $messageLocalizer ) {
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @return string HTML
	 */
	public function getHeaderHtml(): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-selection-container' ],
			$this->buildSideSelectorLabel( 'twocolconflict-split-select-all' ) .
			Html::rawElement(
				'div',
				[ 'class' => [
					'mw-twocolconflict-split-selection',
					'mw-twocolconflict-split-selection-header',
				] ],
				Html::rawElement( 'div', [], new RadioInputWidget( [
					'name' => 'mw-twocolconflict-side-selector',
					'title' => $this->messageLocalizer->msg(
						'twocolconflict-split-select-all-other-tooltip'
					)->text(),
					'value' => 'other',
					'tabIndex' => '1',
				] ) ) .
				Html::rawElement( 'div', [], new RadioInputWidget( [
					'name' => 'mw-twocolconflict-side-selector',
					'title' => $this->messageLocalizer->msg(
						'twocolconflict-split-select-all-your-tooltip'
					)->text(),
					'value' => 'your',
					'tabIndex' => '1',
				] ) )
			)
		);
	}

	/**
	 * @param int $rowNum Identifier for this line.
	 *
	 * @return string HTML
	 */
	public function getRowHtml( int $rowNum ): string {
		return Html::rawElement(
			'div',
			// Note: This CSS class is currently unused
			[ 'class' => 'mw-twocolconflict-split-selection-container' ],
			$this->buildSideSelectorLabel( 'twocolconflict-split-choose-version' ) .
			Html::rawElement(
				'div',
				[ 'class' => [
					'mw-twocolconflict-split-selection',
					'mw-twocolconflict-split-selection-row'
				] ],
				Html::rawElement( 'div', [], new RadioInputWidget( [
					'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
					'title' => $this->messageLocalizer->msg(
						'twocolconflict-split-select-other-tooltip'
					)->text(),
					'value' => 'other',
					'tabIndex' => '1',
				] ) ) .
				Html::rawElement( 'div', [], new RadioInputWidget( [
					'name' => 'mw-twocolconflict-side-selector[' . $rowNum . ']',
					'title' => $this->messageLocalizer->msg(
						'twocolconflict-split-select-your-tooltip'
					)->text(),
					'value' => 'your',
					'selected' => true,
					'tabIndex' => '1',
				] ) )
			)
		);
	}

	/**
	 * @param string $msg
	 *
	 * @return string HTML
	 */
	private function buildSideSelectorLabel( string $msg ): string {
		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-selector-label' ],
			Html::element(
				'span',
				[],
				$this->messageLocalizer->msg( $msg )->text()
			)
		);
	}

}
