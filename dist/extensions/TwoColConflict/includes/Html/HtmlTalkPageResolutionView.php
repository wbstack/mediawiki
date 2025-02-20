<?php

namespace TwoColConflict\Html;

use MediaWiki\Html\Html;
use MessageLocalizer;
use OOUI\ButtonWidget;
use OOUI\FieldLayout;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use OOUI\RadioInputWidget;
use TwoColConflict\SplitConflictUtils;

/**
 * TODO: Clean up, maybe CSS class names should match change type, and "split" replaced with
 *  "single" where appropriate.
 *
 * @license GPL-2.0-or-later
 */
class HtmlTalkPageResolutionView {

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
	 * @param array[] $unifiedDiff A list of changes as created by the AnnotatedHtmlDiffFormatter
	 * @param int $otherIndex
	 * @param int $yourIndex
	 * @param bool $isBetaFeature
	 *
	 * @return string HTML
	 */
	public function getHtml(
		array $unifiedDiff,
		int $otherIndex,
		int $yourIndex,
		bool $isBetaFeature
	): string {
		$out = $this->getMessageBox(
			'twocolconflict-talk-header-overview', 'error', 'mw-twocolconflict-overview' );
		$hintMsg = $isBetaFeature ?
			'twocolconflict-split-header-hint-beta' : 'twocolconflict-split-header-hint';
		$out .= $this->getMessageBox( $hintMsg, 'notice' );

		$rows = '';
		foreach ( $unifiedDiff as $i => $changeSet ) {
			$text = $changeSet['copytext'] ?? $changeSet['newtext'];
			switch ( $i ) {
				case $otherIndex:
					$rows .= $this->buildConflictingTalkRow(
						$text,
						$i,
						'delete',
						'other',
						true,
						'twocolconflict-talk-conflicting'
					);

					$rows .= Html::rawElement(
						'div',
						[ 'class' => 'mw-twocolconflict-single-swap-button-container' ],
						new ButtonWidget( [
							'infusable' => true,
							'framed' => true,
							'icon' => 'markup',
							'title' => $this->messageLocalizer->msg(
								'twocolconflict-talk-switch-tooltip'
							)->text(),
							'classes' => [ 'mw-twocolconflict-single-swap-button' ],
							'tabIndex' => '1'
						] )
					);

					break;
				case $yourIndex:
					$rows .= $this->buildConflictingTalkRow(
						$text,
						$i,
						'add',
						'your',
						false,
						'twocolconflict-talk-your'
					);
					break;
				default:
					$rows .= $this->buildCopyRow( $text, $i );
			}
		}
		// this will allow CSS formatting with :first-of-type
		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-single-column-rows' ],
			$rows
		);

		$out .= $this->buildOrderSelector() .
			Html::hidden( 'mw-twocolconflict-single-column-view', true );

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-view mw-twocolconflict-single-column-view' ],
			$out
		);
	}

	private function wrapRow( string $html, bool $isConflicting = false ): string {
		$class = [ 'mw-twocolconflict-single-row' ];
		if ( $isConflicting ) {
			$class[] = 'mw-twocolconflict-conflicting-talk-row';
		}
		return Html::rawElement( 'div', [ 'class' => $class ], $html );
	}

	private function buildOrderSelector(): string {
		$out = new FieldsetLayout( [
			'label' => $this->messageLocalizer->msg( 'twocolconflict-talk-reorder-prompt' )->text(),
			'items' => [
				new FieldLayout(
					new RadioInputWidget( [
						'name' => 'mw-twocolconflict-reorder',
						'value' => 'reverse',
						'tabIndex' => '1',
					] ),
					[
						'align' => 'inline',
						'label' => $this->messageLocalizer->msg( 'twocolconflict-talk-reverse-order' )->text(),
					]
				),
				new FieldLayout(
					new RadioInputWidget( [
						'name' => 'mw-twocolconflict-reorder',
						'value' => 'no-change',
						'selected' => true,
						'tabIndex' => '1',
					] ),
					[
						'align' => 'inline',
						'label' => $this->messageLocalizer->msg( 'twocolconflict-talk-same-order' )->text(),
					]
				),
			],
		] );

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-order-selector' ],
			$out
		);
	}

	private function buildConflictingTalkRow(
		string $rawText,
		int $rowNum,
		string $classSuffix,
		string $changeType,
		bool $isDisabled,
		string $conflictingTalkLabel
	): string {
		$out = Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-conflicting-talk-label' ],
			Html::rawElement(
				'span',
				[],
				Html::element(
					'span',
					[ 'class' => 'mw-twocolconflict-split-' . $classSuffix ],
					$this->messageLocalizer->msg( $conflictingTalkLabel )->text()
				)
			)
		);

		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-' . $classSuffix . ' mw-twocolconflict-single-column' ],
			$this->editableTextComponent->getHtml(
				htmlspecialchars( $rawText ), $rawText, $rowNum, $changeType, $isDisabled )
		);
		return $this->wrapRow( $out, true );
	}

	private function buildCopyRow(
		string $rawText,
		int $rowNum
	): string {
		$out = Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-copy mw-twocolconflict-single-column' ],
			$this->editableTextComponent->getHtml(
				htmlspecialchars( $rawText ), $rawText, $rowNum, 'copy', true )
		);
		return $this->wrapRow( $out );
	}

	private function getMessageBox( string $messageKey, string $type, string ...$classes ): string {
		$html = $this->messageLocalizer->msg( $messageKey )->parse();
		// Force feedback links to be opened in a new tab, and not lose the edit
		$html = SplitConflictUtils::addTargetBlankToLinks( $html );
		return ( new MessageWidget( [
			'label' => new HtmlSnippet( $html ),
			'type' => $type,
		] ) )
			->addClasses( [ 'mw-twocolconflict-messageWidget', ...$classes ] )
			->toString();
	}

}
