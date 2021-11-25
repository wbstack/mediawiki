<?php

declare( strict_types = 1 );
namespace Wikibase\Repo\Hooks;

use MediaWiki\Page\Hook\ArticleDeleteCompleteHook;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\EntityIdLookup;
use Wikibase\Repo\ChangeModification\DispatchChangeDeletionNotificationJob;

/**
 * Hook for dispatching DeleteDispatchNotificationJob on repo which in turn will fetch archived revisions
 * and dispatch deletion jobs on the clients.
 *
 * @license GPL-2.0-or-later
 */
class DeleteDispatcher implements ArticleDeleteCompleteHook {

	/** @var callable */
	private $jobQueueGroupFactory;

	/** @var EntityIdLookup */
	private $entityIdLookup;

	/** @var array */
	private $localClientDatabases;

	/**
	 * @param callable $jobQueueGroupFactory
	 * @param EntityIdLookup $entityIdLookup
	 * @param array $localClientDatabases
	 */
	public function __construct(
		callable $jobQueueGroupFactory,
		EntityIdLookup $entityIdLookup,
		array $localClientDatabases
	) {
		$this->jobQueueGroupFactory = $jobQueueGroupFactory;
		$this->entityIdLookup = $entityIdLookup;
		$this->localClientDatabases = $localClientDatabases;
	}

	public static function factory(
		EntityIdLookup $entityIdLookup,
		SettingsArray $repoSettings
	): self {
		return new self(
			'JobQueueGroup::singleton',
			$entityIdLookup,
			$repoSettings->getSetting( 'localClientDatabases' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function onArticleDeleteComplete( $wikiPage, $user, $reason, $id, $content, $logEntry, $archivedRevisionCount ) {
		if ( $archivedRevisionCount === 0 || empty( $this->localClientDatabases ) ) {
			return true;
		}

		$title = $wikiPage->getTitle();

		// Abort if not entityId
		$entityId = $this->entityIdLookup->getEntityIdForTitle( $title );
		if ( $entityId === null ) {
			return true;
		}

		$jobParams = [
			"pageId" => $id,
			"archivedRevisionCount" => $archivedRevisionCount
		];
		$job = new DispatchChangeDeletionNotificationJob( $title, $jobParams );

		call_user_func( $this->jobQueueGroupFactory, false )->push( $job );
	}

}
