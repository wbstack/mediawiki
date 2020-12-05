<?php

namespace TwoColConflict\Html;

use Html;
use Language;
use Linker;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use Message;
use MessageLocalizer;
use OOUI\HtmlSnippet;
use OOUI\MessageWidget;
use SpecialPage;
use Title;
use TwoColConflict\SplitConflictUtils;
use User;
use Wikimedia\Timestamp\ConvertibleTimestamp;
use WikiPage;

/**
 * @license GPL-2.0-or-later
 * @author Andrew Kostka <andrew.kostka@wikimedia.de>
 */
class HtmlSplitConflictHeader {

	/**
	 * @var LinkTarget
	 */
	private $title;

	/**
	 * @var RevisionRecord|null
	 */
	private $revision;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var Language
	 */
	private $language;

	/**
	 * @var MessageLocalizer
	 */
	private $messageLocalizer;

	/**
	 * @var ConvertibleTimestamp
	 */
	private $now;

	/**
	 * @var string
	 */
	private $newEditSummary;

	/**
	 * @var LinkRenderer
	 */
	private $linkRenderer;

	/**
	 * @param Title $title
	 * @param User $user
	 * @param string $newEditSummary
	 * @param Language $language
	 * @param MessageLocalizer $messageLocalizer
	 * @param string|int|false $now Current time for testing. Any value the ConvertibleTimestamp
	 *  class accepts. False for the current time.
	 * @param RevisionRecord|null $revision Latest revision for testing, derived from the
	 *  title otherwise.
	 */
	public function __construct(
		Title $title,
		User $user,
		string $newEditSummary,
		Language $language,
		MessageLocalizer $messageLocalizer,
		$now = false,
		RevisionRecord $revision = null
	) {
		$this->title = $title;
		$this->revision = $revision ?? $this->getLatestRevision();
		$this->user = $user;
		$this->language = $language;
		$this->messageLocalizer = $messageLocalizer;
		$this->now = new ConvertibleTimestamp( $now );
		$this->newEditSummary = $newEditSummary;
		// TODO inject?
		$this->linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	}

	/**
	 * @return RevisionRecord|null
	 */
	private function getLatestRevision() : ?RevisionRecord {
		$wikiPage = WikiPage::factory( $this->title );
		/** @see https://phabricator.wikimedia.org/T203085 */
		$wikiPage->loadPageData( WikiPage::READ_LATEST );
		return $wikiPage->getRevisionRecord();
	}

	/**
	 * @param bool $isUsedAsBetaFeature
	 *
	 * @return string HTML
	 */
	public function getHtml( bool $isUsedAsBetaFeature = false ) : string {
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

	private function buildCurrentVersionHeader() : string {
		$dateTime = $this->messageLocalizer->msg( 'just-now' )->text();
		$userTools = '';
		$summary = '';

		if ( $this->revision ) {
			$dateTime = $this->getFormattedDateTime( $this->revision->getTimestamp() );
			// FIXME: This blocks us from having pure unit tests for this class
			$userTools = Linker::revUserTools( $this->revision );

			$comment = $this->revision->getComment( RevisionRecord::FOR_THIS_USER, $this->user );
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

	private function buildYourVersionHeader() : string {
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
	) : string {
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
				->rawParams( Linker::formatComment( $summary, $this->title ) );
			$html .= Html::element( 'br' ) .
				Html::rawElement( 'span', [ 'class' => 'comment' ], $summaryMsg->escaped() );
		}

		return Html::rawElement( 'div', [ 'class' => $class ], $html );
	}

	private function getCopyLink() {
		$specialPage = SpecialPage::getTitleValueFor(
			'TwoColConflictProvideSubmittedText',
			$this->title->getPrefixedDBkey()
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

	/**
	 * @param string|null $timestamp
	 *
	 * @return string
	 */
	private function getFormattedDateTime( ?string $timestamp ) : string {
		$diff = ( new ConvertibleTimestamp( $timestamp ?: false ) )->diff( $this->now );

		if ( $diff->days ) {
			return $this->language->userTimeAndDate( $timestamp, $this->user );
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

	private function getMessageBox( string $messageKey, string $type, $classes = [] ) : string {
		$html = $this->messageLocalizer->msg( $messageKey )->parse();
		return ( new MessageWidget( [
			'label' => new HtmlSnippet( SplitConflictUtils::addTargetBlankToLinks( $html ) ),
			'type' => $type,
		] ) )
			->addClasses( array_merge( [ 'mw-twocolconflict-messageWidget' ], (array)$classes ) )
			->toString();
	}

}
