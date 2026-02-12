<?php

namespace TwoColConflict\Html;

use MediaWiki\CommentFormatter\CommentFormatter;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Linker\Linker;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\SpecialPage\SpecialPage;
use MessageLocalizer;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use TwoColConflict\SplitConflictUtils;
use Wikimedia\Rdbms\IDBAccessObject;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSplitConflictHeader {

	private LinkTarget $linkTarget;
	private ?RevisionRecord $revision;
	private Authority $authority;
	private Language $language;
	private MessageLocalizer $messageLocalizer;
	private ConvertibleTimestamp $now;
	private string $newEditSummary;
	private LinkRenderer $linkRenderer;
	private WikiPageFactory $wikiPageFactory;
	private CommentFormatter $commentFormatter;

	/**
	 * @param LinkTarget $linkTarget
	 * @param Authority $authority
	 * @param string $newEditSummary
	 * @param Language $language
	 * @param MessageLocalizer $messageLocalizer
	 * @param CommentFormatter $commentFormatter
	 * @param string|int|false $now Current time for testing. Any value the ConvertibleTimestamp
	 *  class accepts. False for the current time.
	 * @param RevisionRecord|null $revision Latest revision for testing, derived from the
	 *  title otherwise.
	 */
	public function __construct(
		LinkTarget $linkTarget,
		Authority $authority,
		string $newEditSummary,
		Language $language,
		MessageLocalizer $messageLocalizer,
		CommentFormatter $commentFormatter,
		$now = false,
		?RevisionRecord $revision = null
	) {
		// TODO inject?
		$services = MediaWikiServices::getInstance();
		$this->linkRenderer = $services->getLinkRenderer();
		$this->wikiPageFactory = $services->getWikiPageFactory();

		$this->linkTarget = $linkTarget;
		$this->revision = $revision ?? $this->getLatestRevision();
		$this->authority = $authority;
		$this->language = $language;
		$this->messageLocalizer = $messageLocalizer;
		$this->commentFormatter = $commentFormatter;
		$this->now = new ConvertibleTimestamp( $now );
		$this->newEditSummary = $newEditSummary;
	}

	private function getLatestRevision(): ?RevisionRecord {
		$wikiPage = $this->wikiPageFactory->newFromLinkTarget( $this->linkTarget );
		/** @see https://phabricator.wikimedia.org/T203085 */
		$wikiPage->loadPageData( IDBAccessObject::READ_LATEST );
		return $wikiPage->getRevisionRecord();
	}

	/**
	 * @param bool $isUsedAsBetaFeature
	 *
	 * @return string HTML
	 */
	public function getHtml( bool $isUsedAsBetaFeature = false ): string {
		$hintMsg = $isUsedAsBetaFeature
			? 'twocolconflict-split-header-hint-beta'
			: 'twocolconflict-split-header-hint';

		$out = $this->getMessageBox(
			'twocolconflict-split-header-overview', 'error', 'mw-twocolconflict-overview' );
		$out .= $this->getMessageBox( $hintMsg, 'notice' );
		$out .= Html::rawElement(
			'div',
			[ 'class' => 'mw-twocolconflict-split-header' ],
			Html::rawElement(
				'div',
				[ 'class' => 'mw-twocolconflict-split-flex-header' ],
				$this->buildCurrentVersionHeader() .
					( new HtmlSideSelectorComponent( $this->messageLocalizer ) )->getHeaderHtml() .
					$this->buildYourVersionHeader()
			)
		);
		return $out;
	}

	private function buildCurrentVersionHeader(): string {
		$dateTime = $this->messageLocalizer->msg( 'just-now' )->text();
		$userTools = '';
		$summary = '';

		if ( $this->revision ) {
			$dateTime = $this->getFormattedDateTime( $this->revision->getTimestamp() );
			// FIXME: This blocks us from having pure unit tests for this class
			$userTools = Linker::revUserTools( $this->revision );

			$comment = $this->revision->getComment( RevisionRecord::FOR_THIS_USER, $this->authority );
			if ( $comment ) {
				$summary = $comment->text;
			}
		}

		return $this->buildVersionHeader(
			$this->messageLocalizer->msg( 'twocolconflict-split-current-version-header', $dateTime ),
			$this->messageLocalizer->msg( 'twocolconflict-split-saved-at' )->rawParams( $userTools ),
			$summary,
			'mw-twocolconflict-split-current-version-header'
		);
	}

	private function buildYourVersionHeader(): string {
		return $this->buildVersionHeader(
			$this->messageLocalizer->msg( 'twocolconflict-split-your-version-header' ),
			$this->messageLocalizer->msg( 'twocolconflict-split-not-saved-at' ),
			$this->newEditSummary,
			'mw-twocolconflict-split-your-version-header',
			true
		);
	}

	/**
	 * @param Message $dateMsg
	 * @param Message $userMsg
	 * @param string $summary
	 * @param string $class
	 * @param bool|null $showCopy
	 *
	 * @return string HTML
	 */
	private function buildVersionHeader(
		Message $dateMsg,
		Message $userMsg,
		string $summary,
		string $class,
		?bool $showCopy = false
	): string {
		$html = Html::element(
				'span',
				[ 'class' => 'mw-twocolconflict-revision-label' ],
				$dateMsg->text()
			);
		if ( $showCopy ) {
			$html .= $this->getCopyLink();
		}
		$html .= Html::element( 'br' ) .
			Html::rawElement( 'span', [], $userMsg->escaped() );

		if ( $summary !== '' ) {
			$summaryMsg = $this->messageLocalizer->msg( 'parentheses' )
				->rawParams( $this->commentFormatter->format( $summary, $this->linkTarget ) );
			$html .= Html::element( 'br' ) .
				Html::rawElement( 'span', [ 'class' => 'comment' ], $summaryMsg->escaped() );
		}

		return Html::rawElement( 'div', [ 'class' => $class ], $html );
	}

	private function getCopyLink(): string {
		$specialPage = SpecialPage::getTitleValueFor(
			'TwoColConflictProvideSubmittedText',
			(string)$this->linkTarget
		);
		$label = $this->messageLocalizer->msg( 'twocolconflict-copy-tab-action' )->text();
		$tooltip = $this->messageLocalizer->msg( 'twocolconflict-copy-tab-tooltip' )->text();

		$link = $this->linkRenderer->makeKnownLink(
			$specialPage,
			$label,
			[ 'title' => $tooltip, 'target' => '_blank' ]
		);

		return ' ' . Html::rawElement(
			'span',
			[ 'class' => 'mw-twocolconflict-copy-link' ],
			$this->messageLocalizer->msg( 'parentheses' )->rawParams( $link )
		);
	}

	private function getFormattedDateTime( ?string $timestamp ): string {
		$diff = ( new ConvertibleTimestamp( $timestamp ?: false ) )->diff( $this->now );

		if ( $diff->days ) {
			return $this->language->userTimeAndDate( $timestamp, $this->authority->getUser() );
		}

		if ( $diff->h ) {
			$minutes = $diff->i + $diff->s / 60;
			return $this->messageLocalizer->msg( 'hours-ago', round( $diff->h + $minutes / 60 ) )->text();
		}

		if ( $diff->i ) {
			return $this->messageLocalizer->msg( 'minutes-ago', round( $diff->i + $diff->s / 60 ) )->text();
		}

		if ( $diff->s ) {
			return $this->messageLocalizer->msg( 'seconds-ago', $diff->s )->text();
		}

		return $this->messageLocalizer->msg( 'just-now' )->text();
	}

	private function getMessageBox( string $messageKey, string $type, string ...$classes ): string {
		$html = $this->messageLocalizer->msg( $messageKey )->parse();
		return ( new MessageWidget( [
			'label' => new HtmlSnippet( SplitConflictUtils::addTargetBlankToLinks( $html ) ),
			'type' => $type,
		] ) )
			->addClasses( [ 'mw-twocolconflict-messageWidget', ...$classes ] )
			->toString();
	}

}
