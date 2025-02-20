<?php

declare( strict_types = 1 );

namespace Wikibase\Client\Hooks;

use JobQueueError;
use MediaWiki\Content\Content;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\JobQueue\JobQueueGroupFactory;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Page\Hook\ArticleDeleteCompleteHook;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use Psr\Log\LoggerInterface;
use Wikibase\Client\NamespaceChecker;
use Wikibase\Client\Store\ClientStore;
use Wikibase\Client\UpdateRepo\UpdateRepo;
use Wikibase\Client\UpdateRepo\UpdateRepoOnDelete;
use Wikibase\Client\UpdateRepo\UpdateRepoOnMove;
use Wikibase\DataAccess\DatabaseEntitySource;
use Wikibase\Lib\Rdbms\ClientDomainDb;
use Wikibase\Lib\Rdbms\ClientDomainDbFactory;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\SiteLinkLookup;
use WikiPage;

/**
 * This class has a static interface for use with MediaWiki's hook mechanism; the static
 * handler functions will create a new instance of UpdateRepoHookHandlers and then call the
 * corresponding member function on that.
 *
 * @license GPL-2.0-or-later
 * @author Marius Hoch < hoo@online.de >
 */
class UpdateRepoHookHandler implements PageMoveCompleteHook, ArticleDeleteCompleteHook {

	private NamespaceChecker $namespaceChecker;
	private JobQueueGroupFactory $jobQueueGroupFactory;
	private DatabaseEntitySource $entitySource;
	private SiteLinkLookup $siteLinkLookup;
	private LoggerInterface $logger;
	private ClientDomainDb $clientDb;
	private string $siteGlobalID;
	private bool $propagateChangesToRepo;

	public static function factory(
		JobQueueGroupFactory $jobQueueGroupFactory,
		ClientDomainDbFactory $clientDomainDbFactory,
		DatabaseEntitySource $entitySource,
		NamespaceChecker $namespaceChecker,
		SettingsArray $clientSettings,
		ClientStore $store
	): ?self {
		return new self(
			$namespaceChecker,
			$jobQueueGroupFactory,
			$entitySource,
			$store->getSiteLinkLookup(),
			LoggerFactory::getInstance( 'UpdateRepo' ),
			$clientDomainDbFactory->newLocalDb(),
			$clientSettings->getSetting( 'siteGlobalID' ),
			$clientSettings->getSetting( 'propagateChangesToRepo' )
		);
	}

	public function __construct(
		NamespaceChecker $namespaceChecker,
		JobQueueGroupFactory $jobQueueGroupFactory,
		DatabaseEntitySource $entitySource,
		SiteLinkLookup $siteLinkLookup,
		LoggerInterface $logger,
		ClientDomainDb $clientDb,
		string $siteGlobalID,
		bool $propagateChangesToRepo
	) {
		$this->namespaceChecker = $namespaceChecker;
		$this->jobQueueGroupFactory = $jobQueueGroupFactory;
		$this->entitySource = $entitySource;
		$this->siteLinkLookup = $siteLinkLookup;
		$this->logger = $logger;
		$this->clientDb = $clientDb;

		$this->siteGlobalID = $siteGlobalID;
		$this->propagateChangesToRepo = $propagateChangesToRepo;
	}

	/**
	 * @see NamespaceChecker::isWikibaseEnabled
	 */
	private function isWikibaseEnabled( int $namespace ): bool {
		return $this->namespaceChecker->isWikibaseEnabled( $namespace );
	}

	private function makeDelete(
		UserIdentity $user,
		LinkTarget $title
	): UpdateRepoOnDelete {
		return new UpdateRepoOnDelete(
			$this->siteLinkLookup,
			$this->logger,
			$this->clientDb,
			$user,
			$this->siteGlobalID,
			Title::newFromLinkTarget( $title )
		);
	}

	private function makeMove(
		UserIdentity $user,
		LinkTarget $old,
		Linktarget $new
	): UpdateRepoOnMove {
		return new UpdateRepoOnMove(
			$this->siteLinkLookup,
			$this->logger,
			$this->clientDb,
			$user,
			$this->siteGlobalID,
			Title::newFromLinkTarget( $old ),
			Title::newFromLinkTarget( $new )
		);
	}

	/**
	 * Push the $updateRepo to the job queue if applicable.
	 */
	private function applyUpdateRepo(
		UpdateRepo $updateRepo,
		LinkTarget $title
	): void {
		if ( !$updateRepo->isApplicable() ) {
			return;
		}

		try {
			$jobQueueGroup = $this->jobQueueGroupFactory->makeJobQueueGroup(
				$this->entitySource->getDatabaseName()
			);
			$updateRepo->injectJob( $jobQueueGroup );
		} catch ( JobQueueError $e ) {
			// This is not a reason to let an exception bubble up; the messages shown by
			// MovePageNotice and DeletePageNoticeCreator already ask the user to check
			// that the item was correctly updated anyways.
			// (We used to show different messages in this case, but this broke,
			// went unnoticed for years, and the code was removed in T268135 and T353161.)
			wfLogWarning( $e->getMessage() );

			$this->logger->debug(
				'{method}: Failed to inject job: "{msg}"!',
				[
					'method' => __METHOD__,
					'msg' => $e->getMessage(),
				]
			);

		}
	}

	/**
	 * After a page has been deleted also update the item on the repo.
	 * This only works if there's a user account with the same name on the repo.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleDeleteComplete
	 *
	 * @param WikiPage $wikiPage WikiPage that was deleted
	 * @param User $user User that deleted the article
	 * @param string $reason Reason the article was deleted
	 * @param int $id ID of the article that was deleted
	 * @param Content|null $content Content of the deleted page (or null, when deleting a broken page)
	 * @param \ManualLogEntry $logEntry ManualLogEntry used to record the deletion
	 * @param int $archivedRevisionCount Number of revisions archived during the deletion
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onArticleDeleteComplete(
		$wikiPage,
		$user,
		$reason,
		$id,
		$content,
		$logEntry,
		$archivedRevisionCount
	) {
		if ( !$this->propagateChangesToRepo ) {
			return true;
		}

		$updateRepo = $this->makeDelete( $user, $wikiPage->getTitle() );

		$this->applyUpdateRepo( $updateRepo, $wikiPage->getTitle() );

		return true;
	}

	/**
	 * After a page has been moved also update the item on the repo.
	 * This only works if there's a user account with the same name on the repo.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageMoveComplete
	 *
	 * @param LinkTarget $oldLinkTarget
	 * @param LinkTarget $newLinkTarget
	 * @param UserIdentity $userIdentity
	 * @param int $pageId database ID of the page that's been moved
	 * @param int $redirid ID of the created redirect
	 * @param string $reason
	 * @param RevisionRecord $revisionRecord revision created by the move
	 *
	 * @return bool
	 */
	public function onPageMoveComplete(
		$oldLinkTarget,
		$newLinkTarget,
		$userIdentity,
		$pageId,
		$redirid,
		$reason,
		$revisionRecord
	) {
		if ( !$this->propagateChangesToRepo ) {
			return true;
		}

		if ( $this->isWikibaseEnabled( $newLinkTarget->getNamespace() ) ) {
			$updateRepo = $this->makeMove( $userIdentity, $oldLinkTarget, $newLinkTarget );
		} else {
			// page moved to excluded/unsupported namespace, don’t link to $newLinkTarget
			if ( $redirid ) {
				// $oldLinkTarget is now a redirect, keep the link to it
				return true;
			} else {
				// redirect suppressed, remove the sitelink (T261275)
				$updateRepo = $this->makeDelete( $userIdentity, $oldLinkTarget );
			}
		}

		$this->applyUpdateRepo( $updateRepo, $newLinkTarget );

		return true;
	}

}
