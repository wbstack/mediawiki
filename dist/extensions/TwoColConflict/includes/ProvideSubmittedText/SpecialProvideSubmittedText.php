<?php

namespace TwoColConflict\ProvideSubmittedText;

use MediaWiki\EditPage\TextboxBuilder;
use MediaWiki\Html\Html;
use MediaWiki\Page\PageIdentity;
use MediaWiki\SpecialPage\UnlistedSpecialPage;
use MediaWiki\Title\Title;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use TwoColConflict\TwoColConflictContext;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\Stats\StatsFactory;

/**
 * Special page allows users to see their originally submitted text while they
 * encounter an edit conflict.
 *
 * @license GPL-2.0-or-later
 * @author Christoph Jauera <christoph.jauera@wikimedia.de>
 */
class SpecialProvideSubmittedText extends UnlistedSpecialPage {

	private TwoColConflictContext $twoColConflictContext;
	private SubmittedTextCache $textCache;
	private StatsFactory $statsFactory;

	public function __construct(
		TwoColConflictContext $twoColConflictContext,
		BagOStuff $textCache,
		StatsFactory $statsFactory
	) {
		parent::__construct( 'TwoColConflictProvideSubmittedText' );
		$this->twoColConflictContext = $twoColConflictContext;
		$this->textCache = new SubmittedTextCache( $textCache );
		$this->statsFactory = $statsFactory;
	}

	/**
	 * @param string|null $subPage
	 */
	public function execute( $subPage ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.TwoColConflict.SplitCss' );
		$out->enableOOUI();
		$this->statsFactory->getCounter( 'TwoColConflict_copy_special_load_total' )
			->copyToStatsdAt( 'TwoColConflict.copy.special.load' )
			->increment();

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

		$out->setPageTitleMsg(
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

		$this->statsFactory->getCounter( 'TwoColConflict_copy_special_retrieved_total' )
			->copyToStatsdAt( 'TwoColConflict.copy.special.retrieved' )
			->increment();

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

	private function getTextHeaderLabelHtml(): string {
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
	private function getTextAreaHtml( string $text, PageIdentity $page ): string {
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

	private function getFooterHtml(): string {
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
