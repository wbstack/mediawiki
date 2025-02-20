<?php

namespace TwoColConflict\Html;

use MediaWiki\Html\Html;
use MessageLocalizer;
use OOUI\HtmlSnippet;
use OOUI\IconWidget;
use OOUI\MessageWidget;
use TwoColConflict\SplitConflictUtils;

/**
 * @license GPL-2.0-or-later
 */
class CoreUiHintHtml {

	private MessageLocalizer $messageLocalizer;

	public function __construct( MessageLocalizer $messageLocalizer ) {
		$this->messageLocalizer = $messageLocalizer;
	}

	public function getHtml(): string {
		$closeIcon = new IconWidget( [
			'icon' => 'close',
			'title' => $this->messageLocalizer->msg( 'twocolconflict-core-ui-hint-close' )->text(),
		] );
		$hintMessage = $this->messageLocalizer->msg( 'twocolconflict-core-ui-hint' )->parse();

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-core-ui-hint' ],
			Html::check(
				'mw-twocolconflict-disable-core-hint',
				false,
				[ 'id' => 'mw-twocolconflict-disable-core-hint' ]
			) .
			new MessageWidget( [
				'label' => new HtmlSnippet(
					SplitConflictUtils::addTargetBlankToLinks( $hintMessage ) .
					Html::rawElement(
						'label',
						[ 'for' => 'mw-twocolconflict-disable-core-hint' ],
						$closeIcon
					)
				)
			] )
		);
	}

}
