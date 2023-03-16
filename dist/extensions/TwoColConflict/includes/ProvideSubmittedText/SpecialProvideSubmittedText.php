<?php

namespace TwoColConflict\ProvideSubmittedText;

use BagOStuff;
use Html;
use IBufferingStatsdDataFactory;
use MediaWiki\EditPage\TextboxBuilder;
use MediaWiki\Page\PageIdentity;
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

	/** @var TwoColConflictContext */
	private $twoColConflictContext;

	/** @var SubmittedTextCache */
	private $textCache;

	/** @var IBufferingStatsdDataFactory */
	private $statsdDataFactory;

	/**
	 * @param TwoColConflictContext $twoColConflictContext
	 * @param BagOStuff $textCache
	 * @param IBufferingStatsdDataFactory $statsdDataFactory
	 */
	public function __construct(
		TwoColConflictContext $twoColConflictContext,
		BagOStuff $textCache,
		IBufferingStatsdDataFactory $statsdDataFactory
	) {
		parent::__construct( 'TwoColConflictProvideSubmittedText' );
		$this->twoColConflictContext = $twoColConflictContext;
		$this->textCache = new SubmittedTextCache( $textCache );
		$this->statsdDataFactory = $statsdDataFactory;
	}

	/**
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.TwoColConflict.SplitCss' );
		$out->enableOOUI();
		$this->statsdDataFactory->increment( 'TwoColConflict.copy.special.load' );

		$title = Title::newFromDBkey( $subPage ?? '' );
		if ( !$title ) {
			// Should be the same error code as for every malformed title
			$out->setStatusCode( 404 );
			$out->addHTML( new MessageWidget( [
				'label' => $this->msg( 'twocolconflict-special-malformed-title' )->text(),
				'type' => 'error',
			] ) );
			return;
		}

		$out->setPageTitle(
			$this->msg( 'editconflict', $title->getPrefixedText() )
		);

		$text = $this->textCache->fetchText(
			$subPage,
			$out->getUser(),
			$out->getRequest()->getSessionId()
		);

		if ( !$text ) {
			// 410 means "gone", which is quite literally what happened here
			$out->setStatusCode( 410 );
			$out->addHTML( new MessageWidget( [
				'label' => $this->msg( 'twocolconflict-special-expired' )->text(),
				'type' => 'warning',
			] ) );
			return;
		}

		$this->statsdDataFactory->increment( 'TwoColConflict.copy.special.retrieved' );

		$html = $this->getHeaderHintsHtml();
		$html .= $this->getTextHeaderLabelHtml();
		$html .= $this->getTextAreaHtml( $text, $title );
		$html .= $this->getFooterHtml();

		$out->addHTML( $html );
	}

	private function getHeaderHintsHtml(): string {
		$hintMsg = $this->twoColConflictContext->isUsedAsBetaFeature()
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

	/**
	 * @param string $text
	 * @param PageIdentity $page Used to create the lang="…" and dir="…" attributes
	 * @return string
	 */
	private function getTextAreaHtml( string $text, PageIdentity $page ) {
		$builder = new TextboxBuilder();
		$attribs = $builder->mergeClassesIntoAttributes(
			[ 'mw-twocolconflict-submitted-text' ],
			[ 'readonly', 'tabindex' => 1 ]
		);

		$attribs = $builder->buildTextboxAttribs(
			'wpTextbox2',
			$attribs,
			$this->getUser(),
			$page
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

	private function getMessageBox( string $messageKey ): string {
		$html = $this->msg( $messageKey )->parse();
		return ( new MessageWidget( [
			'label' => new HtmlSnippet( $html ),
			'type' => 'notice',
		] ) )
			->addClasses( [ 'mw-twocolconflict-messageWidget' ] )
			->toString();
	}

}
