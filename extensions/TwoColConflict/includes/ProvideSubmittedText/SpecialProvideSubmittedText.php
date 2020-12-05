<?php

namespace TwoColConflict\ProvideSubmittedText;

use Html;
use MediaWiki\EditPage\TextboxBuilder;
use MediaWiki\MediaWikiServices;
use ObjectCache;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use Title;
use TwoColConflict\TwoColConflictContext;
use UnlistedSpecialPage;

/**
 * Special page allows users to see their originally submitted text while they
 * encounter an edit conflict.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SpecialProvideSubmittedText extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'TwoColConflictProvideSubmittedText' );
	}

	/**
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.TwoColConflict.SplitCss' );
		$out->enableOOUI();
		$stats = MediaWikiServices::getInstance()->getStatsdDataFactory();
		$stats->increment( 'TwoColConflict.copy.special.load' );

		$title = Title::newFromDBkey( $subPage ?? '' );
		if ( !$title ) {
			// TODO: Return with a 404 ("Not Found") and show an error message
			return;
		}

		$out->setPageTitle(
			$this->msg( 'editconflict', $title->getPrefixedText() )
		);

		$textCache = new SubmittedTextCache( ObjectCache::getInstance( 'db-replicated' ) );
		$text = $textCache->fetchText(
			$subPage,
			$out->getUser(),
			$out->getRequest()->getSessionId()
		);

		if ( !$text ) {
			// TODO Return with a 410 ("Gone") and show an error message
			return;
		}

		$stats->increment( 'TwoColConflict.copy.special.retrieved' );

		$html = $this->getHeaderHintsHtml();
		$html .= $this->getTextHeaderLabelHtml();
		$html .= $this->getTextAreaHtml( $text );
		$html .= $this->getFooterHtml();

		$out->addHTML( $html );
	}

	private function getHeaderHintsHtml() : string {
		/** @var TwoColConflictContext $twoColContext */
		$twoColContext = MediaWikiServices::getInstance()->getService( 'TwoColConflictContext' );
		$hintMsg = $twoColContext->isUsedAsBetaFeature()
			? 'twocolconflict-split-header-hint-beta'
			: 'twocolconflict-split-header-hint';

		$out = $this->getMessageBox( 'twocolconflict-special-header-overview' );
		$out .= $this->getMessageBox( $hintMsg );

		return $out;
	}

	private function getTextHeaderLabelHtml() {
		$html = Html::element(
			'span',
			[ 'class' => 'mw-twocolconflict-revision-label' ],
			$this->msg( 'twocolconflict-split-your-version-header' )->text()
		);
		$html .= Html::element( 'br' );
		$html .= Html::element(
			'span',
			[],
			$this->msg( 'twocolconflict-special-not-saved' )->text()
		);

		return Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-special-your-version-header' ],
			$html
		);
	}

	private function getTextAreaHtml( $text ) {
		$builder = new TextboxBuilder();
		$attribs = $builder->mergeClassesIntoAttributes(
			[ 'mw-twocolconflict-submitted-text' ],
			[ 'readonly', 'tabindex' => 1 ]
		);

		$attribs = $builder->buildTextboxAttribs(
			'wpTextbox2',
			$attribs,
			$this->getUser(),
			$this->getPageTitle()
		);

		return Html::element( 'span', [], $this->msg( 'twocolconflict-special-textarea-hint' )->text() ) .
			Html::textarea(
				'wpTextbox2',
				$builder->addNewLineAtEnd( $text ),
				$attribs
			);
	}

	private function getFooterHtml() {
		return Html::element( 'p', [], $this->msg( 'twocolconflict-special-footer-hint' )->text() );
	}

	private function getMessageBox( string $messageKey ) : string {
		$html = $this->msg( $messageKey )->parse();
		return ( new MessageWidget( [
			'label' => new HtmlSnippet( $html ),
			'type' => 'notice',
		] ) )
			->addClasses( [ 'mw-twocolconflict-messageWidget' ] )
			->toString();
	}
}
