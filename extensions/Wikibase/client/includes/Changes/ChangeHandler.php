<?php

namespace Wikibase\Client\Changes;

use Hooks;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SiteLookup;
use Title;
use TitleFactory;
use Wikibase\Client\Usage\PageEntityUsages;
use Wikibase\Lib\Changes\Change;
use Wikibase\Lib\Changes\EntityChange;

/**
 * Interface for change handling. Whenever a change is detected,
 * it should be fed to this service which then takes care handling it.
 *
 * @see @ref md_docs_topics_change-propagation for an overview of the change propagation mechanism.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class ChangeHandler {

	/**
	 * @var AffectedPagesFinder
	 */
	private $affectedPagesFinder;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * @var PageUpdater
	 */
	private $updater;

	/**
	 * @var ChangeRunCoalescer
	 */
	private $changeRunCoalescer;

	/**
	 * @var SiteLookup
	 */
	private $siteLookup;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $injectRecentChanges;

	/**
	 * @param AffectedPagesFinder $affectedPagesFinder
	 * @param TitleFactory $titleFactory
	 * @param PageUpdater $updater
	 * @param ChangeRunCoalescer $changeRunCoalescer
	 * @param SiteLookup $siteLookup
	 * @param LoggerInterface $logger
	 * @param bool $injectRecentChanges
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		AffectedPagesFinder $affectedPagesFinder,
		TitleFactory $titleFactory,
		PageUpdater $updater,
		ChangeRunCoalescer $changeRunCoalescer,
		SiteLookup $siteLookup,
		LoggerInterface $logger,
		$injectRecentChanges = true
	) {
		if ( !is_bool( $injectRecentChanges ) ) {
			throw new InvalidArgumentException( '$injectRecentChanges must be a bool' );
		}

		$this->affectedPagesFinder = $affectedPagesFinder;
		$this->titleFactory = $titleFactory;
		$this->updater = $updater;
		$this->changeRunCoalescer = $changeRunCoalescer;
		$this->siteLookup = $siteLookup;
		$this->logger = $logger;
		$this->injectRecentChanges = $injectRecentChanges;
	}

	/**
	 * @param EntityChange[] $changes
	 * @param array $rootJobParams any relevant root job parameters to be inherited by new jobs.
	 */
	public function handleChanges( array $changes, array $rootJobParams = [] ) {
		$changes = $this->changeRunCoalescer->transformChangeList( $changes );

		if ( !Hooks::run( 'WikibaseHandleChanges', [ $changes, $rootJobParams ] ) ) {
			return;
		}

		foreach ( $changes as $change ) {
			if ( !Hooks::run( 'WikibaseHandleChange', [ $change, $rootJobParams ] ) ) {
				continue;
			}

			$this->handleChange( $change, $rootJobParams );
		}
	}

	/**
	 * Main entry point for handling changes
	 *
	 * @todo process multiple changes at once!
	 *
	 * @param EntityChange $change
	 * @param array $rootJobParams any relevant root job parameters to be inherited by new jobs.
	 */
	public function handleChange( EntityChange $change, array $rootJobParams = [] ) {
		$changeId = $this->getChangeIdForLog( $change );

		$this->logger->debug(
			'{method}: handling change #{changeId} ({changeType})',
			[
				'method' => __METHOD__,
				'changeId' => $changeId,
				'changeType' => $change->getType(),
			]
		);

		$usagesPerPage = $this->affectedPagesFinder->getAffectedUsagesByPage( $change );

		$this->logger->debug(
			'{method}: updating {pageCount} page(s) for change #{changeId}.',
			[
				'method' => __METHOD__,
				'changeId' => $changeId,
				'pageCount' => count( $usagesPerPage ),
			]
		);

		// if no usages we can abort early
		if ( $usagesPerPage === [] ) {
			return;
		}

		// Run all updates on all affected pages
		$titlesToUpdate = $this->getTitlesForUsages( $usagesPerPage );

		// if no titles we can abort early
		if ( $titlesToUpdate === [] ) {
			return;
		}

		// NOTE: deduplicate
		$titleBatchSignature = $this->getTitleBatchSignature( $titlesToUpdate );
		$rootJobParams['rootJobSignature'] = $titleBatchSignature;

		if ( !isset( $rootJobParams['rootJobTimestamp'] ) ) {
			$rootJobParams['rootJobTimestamp'] = wfTimestampNow();
		}

		$this->updater->purgeWebCache(
			$titlesToUpdate,
			$rootJobParams,
			$change->getAction(),
			$change->hasField( 'user_id' ) ? 'uid:' . $change->getUserId() : 'uid:?'
		);

		// Removing root job timestamp to make it work: T233520
		$refreshLinksRootParams = $rootJobParams;
		unset( $refreshLinksRootParams['rootJobTimestamp'] );

		$this->updater->scheduleRefreshLinks(
			$titlesToUpdate,
			$refreshLinksRootParams,
			$change->getAction(),
			'uid:' . ( $change->getUserId() ?: '?' )
		);

		// NOTE: signature depends on change ID, effectively disabling deduplication
		$changeSignature = $this->getChangeSignature( $change );
		$rootJobParams['rootJobSignature'] = $titleBatchSignature . '&' . $changeSignature;
		if ( $this->injectRecentChanges ) {
			$this->updater->injectRCRecords( $titlesToUpdate, $change, $rootJobParams );
		}
	}

	/**
	 * @param Title[] $titles
	 *
	 * @return string a signature based on the hash of the given titles
	 */
	private function getTitleBatchSignature( array $titles ) {
		$pages = [];

		/** @see WikiPageUpdater::getPageParamForRefreshLinksJob */
		foreach ( $titles as $title ) {
			$id = $title->getArticleID();
			$pages[$id] = [ $title->getNamespace(), $title->getDBkey() ];
		}

		ksort( $pages );
		return 'title-batch:' . sha1( json_encode( $pages ) );
	}

	/**
	 * @param EntityChange $change
	 *
	 * @return string a signature representing the change's identity.
	 */
	private function getChangeSignature( EntityChange $change ) {
		if ( $change->getId() ) {
			return 'change-id:' . $change->getId();
		} else {
			// synthetic change!
			$changeData = $change->getFields();

			if ( isset( $changeData['info']['change-ids'] ) ) {
				$ids = $changeData['info']['change-ids'];
				sort( $ids );
				return 'change-batch:' . implode( ',', $ids );
			} else {
				ksort( $changeData );
				return 'change-hash:' . sha1( json_encode( $changeData ) );
			}
		}
	}

	/**
	 * @param PageEntityUsages[] $usagesPerPage
	 *
	 * @return Title[]
	 */
	private function getTitlesForUsages( $usagesPerPage ) {
		$pageIds = [];

		foreach ( $usagesPerPage as $usages ) {
			$pageIds[] = $usages->getPageId();
		}

		return $this->titleFactory->newFromIDs( $pageIds );
	}

	/**
	 * Returns a human readable change ID, containing multiple IDs in case of a
	 * coalesced change.
	 *
	 * @param Change $change
	 *
	 * @return string
	 */
	private function getChangeIdForLog( Change $change ) {
		if ( $change instanceof EntityChange ) {
			$info = $change->getInfo();

			if ( isset( $info['change-ids'] ) ) {
				return implode( '|', $info['change-ids'] );
			}
		}

		return $change->getId();
	}

}
