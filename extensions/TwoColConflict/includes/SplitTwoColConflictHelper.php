<?php

namespace TwoColConflict;

use Html;
use IBufferingStatsdDataFactory;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\EditPage\TextConflictHelper;
use ObjectCache;
use OutputPage;
use ParserOptions;
use Title;
use TwoColConflict\Html\HtmlEditableTextComponent;
use TwoColConflict\Html\HtmlSplitConflictHeader;
use TwoColConflict\Html\HtmlSplitConflictView;
use TwoColConflict\Html\HtmlTalkPageResolutionView;
use TwoColConflict\ProvideSubmittedText\SubmittedTextCache;
use TwoColConflict\TalkPageConflict\ResolutionSuggester;
use TwoColConflict\TalkPageConflict\TalkPageResolution;
use User;
use WikitextContent;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class SplitTwoColConflictHelper extends TextConflictHelper {

	/**
	 * @var string
	 */
	private $newEditSummary;

	/**
	 * @var TwoColConflictContext
	 */
	private $twoColContext;

	/**
	 * @var ResolutionSuggester
	 */
	private $resolutionSuggester;

	/**
	 * @param Title $title
	 * @param OutputPage $out
	 * @param IBufferingStatsdDataFactory $stats
	 * @param string $submitLabel
	 * @param string $newEditSummary
	 * @param IContentHandlerFactory $contentHandlerFactory
	 * @param TwoColConflictContext $twoColContext
	 * @param ResolutionSuggester $resolutionSuggester
	 */
	public function __construct(
		Title $title,
		OutputPage $out,
		IBufferingStatsdDataFactory $stats,
		string $submitLabel,
		string $newEditSummary,
		IContentHandlerFactory $contentHandlerFactory,
		TwoColConflictContext $twoColContext,
		ResolutionSuggester $resolutionSuggester
	) {
		parent::__construct( $title, $out, $stats, $submitLabel, $contentHandlerFactory );

		$this->newEditSummary = $newEditSummary;
		$this->twoColContext = $twoColContext;
		$this->resolutionSuggester = $resolutionSuggester;

		$this->out->enableOOUI();
		$this->out->addModuleStyles( [
			'oojs-ui.styles.icons-editing-core',
			'oojs-ui.styles.icons-editing-advanced',
			'oojs-ui.styles.icons-movement'
		] );
	}

	/**
	 * @inheritDoc
	 */
	public function incrementConflictStats( User $user = null ) {
		parent::incrementConflictStats( $user );
		$this->stats->increment( 'TwoColConflict.conflict' );
		// XXX This is copied directly from core and we may be able to refactor something here.
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			$this->stats->increment(
				'TwoColConflict.conflict.byNamespaceId.' . $this->title->getNamespace()
			);
		}
		if ( $user ) {
			$this->incrementStatsByUserEdits( $user->getEditCount(), 'TwoColConflict.conflict' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function incrementResolvedStats( User $user = null ) {
		parent::incrementResolvedStats( $user );
		$this->stats->increment( 'TwoColConflict.conflict.resolved' );
		// XXX This is copied directly from core and we may be able to refactor something here.
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			$this->stats->increment(
				'TwoColConflict.conflict.resolved.byNamespaceId.' . $this->title->getNamespace()
			);
		}
		if ( $user ) {
			$this->incrementStatsByUserEdits(
				$user->getEditCount(), 'TwoColConflict.conflict.resolved'
			);
		}
	}

	/**
	 * Replace default header for explaining the conflict screen.
	 *
	 * @return string
	 */
	public function getExplainHeader() {
		// TODO
		return '';
	}

	/**
	 * Shows the diff part in the original conflict handling. Is not
	 * used and overwritten by a simple container for the result text.
	 *
	 * @param array $customAttribs
	 *
	 * @return string HTML
	 */
	public function getEditConflictMainTextBox( array $customAttribs = [] ) {
		return '';
	}

	/**
	 * Shows the diff part in the original conflict handling. Is not
	 * used and overwritten.
	 */
	public function showEditFormTextAfterFooters() {
	}

	/**
	 * Build HTML that will be added before the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlBeforeContent() {
		$storedLines = SplitConflictUtils::splitText( $this->storedversion );
		$yourLines = SplitConflictUtils::splitText( $this->yourtext );

		$suggestion = $this->twoColContext->shouldTalkPageSuggestionBeConsidered( $this->title )
			? $this->resolutionSuggester->getResolutionSuggestion( $storedLines, $yourLines )
			: null;
		if ( $suggestion ) {
			$conflictView = $this->buildResolutionSuggestionView( $suggestion );
		} else {
			$conflictView = $this->buildEditConflictView( $storedLines, $yourLines );
		}

		$this->setSubmittedTextCache();

		return Html::hidden( 'wpTextbox1', $this->storedversion ) .
			$conflictView .
			$this->buildRawTextsHiddenFields();
	}

	/**
	 * Build HTML content that will be added after the default edit form.
	 *
	 * @return string
	 */
	public function getEditFormHtmlAfterContent() {
		$this->out->addModuleStyles( 'ext.TwoColConflict.SplitCss' );
		$this->out->addModules( 'ext.TwoColConflict.SplitJs' );
		return '';
	}

	private function getPreSaveTransformedLines() {
		$user = $this->out->getUser();

		$content = new WikitextContent( $this->yourtext );
		$parserOptions = new ParserOptions( $user, $this->out->getLanguage() );
		// @phan-suppress-next-line PhanUndeclaredMethod
		$previewWikitext = $content->preSaveTransform( $this->title, $user, $parserOptions )->getText();
		return SplitConflictUtils::splitText( $previewWikitext );
	}

	/**
	 * Build HTML that will add the textbox with the unified diff.
	 *
	 * @param string[] $storedLines
	 * @param string[] $yourLines
	 *
	 * @return string
	 */
	private function buildEditConflictView( array $storedLines, array $yourLines ) : string {
		$user = $this->out->getUser();
		$language = $this->out->getLanguage();
		$formatter = new AnnotatedHtmlDiffFormatter();
		$diff = $formatter->format( $storedLines, $yourLines, $this->getPreSaveTransformedLines() );

		$out = ( new HtmlSplitConflictHeader(
			$this->title,
			$user,
			$this->newEditSummary,
			$language,
			$this->out->getContext()
		) )->getHtml( $this->twoColContext->isUsedAsBetaFeature() );
		// @phan-suppress-next-line SecurityCheck-DoubleEscaped
		$out .= ( new HtmlSplitConflictView(
			new HtmlEditableTextComponent(
				$this->out->getContext(),
				$language,
				$user->getOption( 'editfont' )
			),
			$this->out->getContext()
		) )->getHtml(
			$diff,
			// Note: Can't use getBool() because that discards arrays
			(bool)$this->out->getRequest()->getArray( 'mw-twocolconflict-split-content' )
		);
		return $out;
	}

	private function buildResolutionSuggestionView( TalkPageResolution $suggestion ) : string {
		// @phan-suppress-next-line SecurityCheck-DoubleEscaped
		return ( new HtmlTalkPageResolutionView(
			new HtmlEditableTextComponent(
				$this->out->getContext(),
				$this->out->getLanguage(),
				$this->out->getUser()->getOption( 'editfont' )
			),
			$this->out->getContext()
		) )->getHtml(
			$suggestion->getDiff(),
			$suggestion->getOtherIndex(),
			$suggestion->getYourIndex(),
			$this->twoColContext->isUsedAsBetaFeature()
		);
	}

	/**
	 * Build HTML for the hidden field with the text the user submitted.
	 *
	 * @return string
	 */
	private function buildRawTextsHiddenFields() : string {
		return Html::textarea(
				'mw-twocolconflict-your-text',
				$this->yourtext,
				[
					'class' => 'mw-twocolconflict-your-text',
					'readonly' => true,
					'tabindex' => '-1',
				]
			);
	}

	private function setSubmittedTextCache() {
		$textCache = new SubmittedTextCache( ObjectCache::getInstance( 'db-replicated' ) );
		if ( !$textCache->stashText(
			$this->title->getPrefixedDBkey(),
			$this->out->getUser(),
			$this->out->getRequest()->getSessionId(),
			$this->yourtext
		) ) {
			// TODO: Log error?
		}
	}

}
