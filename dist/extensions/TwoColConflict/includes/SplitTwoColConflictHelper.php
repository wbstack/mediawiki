<?php

namespace TwoColConflict;

use MediaWiki\CommentFormatter\CommentFormatter;
use MediaWiki\Content\IContentHandlerFactory;
use MediaWiki\Content\WikitextContent;
use MediaWiki\EditPage\TextConflictHelper;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use TwoColConflict\Html\HtmlEditableTextComponent;
use TwoColConflict\Html\HtmlSplitConflictHeader;
use TwoColConflict\Html\HtmlSplitConflictView;
use TwoColConflict\Html\HtmlTalkPageResolutionView;
use TwoColConflict\ProvideSubmittedText\SubmittedTextCache;
use TwoColConflict\TalkPageConflict\ResolutionSuggester;
use TwoColConflict\TalkPageConflict\TalkPageResolution;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\Stats\IBufferingStatsdDataFactory;
use Wikimedia\Stats\StatsFactory;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class SplitTwoColConflictHelper extends TextConflictHelper {

	private TwoColConflictContext $twoColContext;
	private ResolutionSuggester $resolutionSuggester;
	private CommentFormatter $commentFormatter;
	private ?SubmittedTextCache $textCache;
	private string $newEditSummary;
	private ?string $editFontOption;

	/**
	 * @param Title $title
	 * @param OutputPage $out
	 * @param IBufferingStatsdDataFactory|StatsFactory $stats
	 * @param string $submitLabel Message key for submit button's label
	 * @param IContentHandlerFactory $contentHandlerFactory
	 * @param TwoColConflictContext $twoColContext
	 * @param ResolutionSuggester $resolutionSuggester
	 * @param CommentFormatter $commentFormatter
	 * @param BagOStuff|null $textCache
	 * @param string $newEditSummary
	 * @param string|null $editFontOption
	 */
	public function __construct(
		Title $title,
		OutputPage $out,
		$stats,
		string $submitLabel,
		IContentHandlerFactory $contentHandlerFactory,
		TwoColConflictContext $twoColContext,
		ResolutionSuggester $resolutionSuggester,
		CommentFormatter $commentFormatter,
		?BagOStuff $textCache = null,
		string $newEditSummary = '',
		?string $editFontOption = null
	) {
		parent::__construct( $title, $out, $stats, $submitLabel, $contentHandlerFactory );

		$this->twoColContext = $twoColContext;
		$this->resolutionSuggester = $resolutionSuggester;
		$this->commentFormatter = $commentFormatter;
		$this->textCache = $textCache ? new SubmittedTextCache( $textCache ) : null;
		$this->newEditSummary = $newEditSummary;
		$this->editFontOption = $editFontOption;

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
	public function incrementConflictStats( ?User $user = null ) {
		parent::incrementConflictStats( $user );
		// XXX This is copied largely from core and we may be able to refactor something here.
		$namespace = 'n/a';
		$userBucket = 'n/a';
		$statsdNamespaces = [ 'TwoColConflict.conflict' ];
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			// getNsText() returns empty string if getNamespace() === NS_MAIN
			$namespace = $this->title->getNsText() ?: 'Main';
			$statsdNamespaces[] = 'TwoColConflict.conflict.byNamespaceId.' . $this->title->getNamespace();
		}
		if ( $user ) {
			$userBucket = $this->getUserBucket( $user->getEditCount() );
			$statsdNamespaces[] = 'TwoColConflict.conflict.byUserEdits.' . $userBucket;
		}

		$this->stats->withComponent( 'TwoColConflict' )
			->getCounter( 'edit_failure_total' )
			->setLabel( 'namespace', $namespace )
			->setLabel( 'user_bucket', $userBucket )
			->copyToStatsdAt( $statsdNamespaces )
			->increment();
	}

	/**
	 * @inheritDoc
	 */
	public function incrementResolvedStats( ?User $user = null ) {
		parent::incrementResolvedStats( $user );
		// XXX This is copied largely from core and we may be able to refactor something here.
		$namespace = 'n/a';
		$userBucket = 'n/a';
		$statsdNamespaces = [ 'TwoColConflict.conflict.resolved' ];
		// Only include 'standard' namespaces to avoid creating unknown numbers of statsd metrics
		if (
			$this->title->getNamespace() >= NS_MAIN &&
			$this->title->getNamespace() <= NS_CATEGORY_TALK
		) {
			// getNsText() returns empty string if getNamespace() === NS_MAIN
			$namespace = $this->title->getNsText() ?: 'Main';
			$statsdNamespaces[] = 'TwoColConflict.conflict.resolved.byNamespaceId.' . $this->title->getNamespace();
		}
		if ( $user ) {
			$userBucket = $this->getUserBucket( $user->getEditCount() );
			$statsdNamespaces[] = 'TwoColConflict.conflict.resolved.byUserEdits.' . $userBucket;
		}

		$this->stats->withComponent( 'TwoColConflict' )
			->getCounter( 'edit_failure_resolved_total' )
			->setLabel( 'namespace', $namespace )
			->setLabel( 'user_bucket', $userBucket )
			->copyToStatsdAt( $statsdNamespaces )
			->increment();
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

		$page = $this->out->getWikiPage();
		$user = $this->out->getUser();
		$suggestion = $this->twoColContext->shouldTalkPageSuggestionBeConsidered( $page, $user )
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

	private function getPreSaveTransformedLines(): array {
		$user = $this->out->getUser();

		$content = new WikitextContent( $this->yourtext );
		$parserOptions = new ParserOptions( $user, $this->out->getLanguage() );
		$contentTransformer = MediaWikiServices::getInstance()->getContentTransformer();
		$content = $contentTransformer->preSaveTransform(
			$content,
			$this->title,
			$user,
			$parserOptions
		);
		// @phan-suppress-next-line PhanUndeclaredMethod
		$previewWikitext = $content->getText();

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
	private function buildEditConflictView( array $storedLines, array $yourLines ): string {
		$user = $this->out->getUser();
		$language = $this->out->getLanguage();
		$formatter = new AnnotatedHtmlDiffFormatter();
		$diff = $formatter->format( $storedLines, $yourLines, $this->getPreSaveTransformedLines() );

		$out = ( new HtmlSplitConflictHeader(
			$this->title,
			$user,
			$this->newEditSummary,
			$language,
			$this->out->getContext(),
			$this->commentFormatter
		) )->getHtml( $this->twoColContext->isUsedAsBetaFeature() );
		// @phan-suppress-next-line SecurityCheck-DoubleEscaped
		$out .= ( new HtmlSplitConflictView(
			new HtmlEditableTextComponent(
				$this->out->getContext(),
				$language,
				$this->editFontOption
			),
			$this->out->getContext()
		) )->getHtml(
			$diff,
			// Note: Can't use getBool() because that discards arrays
			(bool)$this->out->getRequest()->getArray( 'mw-twocolconflict-split-content' )
		);
		return $out;
	}

	private function buildResolutionSuggestionView( TalkPageResolution $suggestion ): string {
		// @phan-suppress-next-line SecurityCheck-DoubleEscaped
		return ( new HtmlTalkPageResolutionView(
			new HtmlEditableTextComponent(
				$this->out->getContext(),
				$this->out->getLanguage(),
				$this->editFontOption
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
	private function buildRawTextsHiddenFields(): string {
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

	private function setSubmittedTextCache(): void {
		if ( $this->textCache && !$this->textCache->stashText(
			$this->title->getPrefixedDBkey(),
			$this->out->getUser(),
			$this->out->getRequest()->getSessionId(),
			$this->yourtext
		) ) {
			// TODO: Log error?
		}
	}

}
