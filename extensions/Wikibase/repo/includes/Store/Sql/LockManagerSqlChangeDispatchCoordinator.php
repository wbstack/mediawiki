<?php

namespace Wikibase\Repo\Store\Sql;

use LockManager;
use Psr\Log\LoggerInterface;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\ILBFactory;

/**
 * SQL-based implementation of ChangeDispatchCoordinator when there is a
 * LockManager implementation provided to be used instead of
 * SqlChangeDisptachCoordinator's own locking.
 *
 * @license GPL-2.0-or-later
 * @author Amir Sarabadani <ladsgroup@gmail.com>
 */
class LockManagerSqlChangeDispatchCoordinator extends SqlChangeDispatchCoordinator {

	/**
	 * @var LockManager
	 */
	private $lockManager;

	/**
	 * @param LockManager $lockManager
	 * @param ILBFactory $LBFactory
	 * @param LoggerInterface $logger
	 * @param string|false $repoDB
	 * @param string $repoSiteId The repo's global wiki ID
	 */
	public function __construct(
		LockManager $lockManager,
		ILBFactory $LBFactory,
		LoggerInterface $logger,
		$repoDB,
		string $repoSiteId
	) {
		$this->lockManager = $lockManager;
		parent::__construct( $repoDB, $repoSiteId, $LBFactory, $logger );
	}

	/**
	 * @see SqlChangeDispatchCoordinator::engageClientLock()
	 *
	 * @param string $lock The name of the lock to engage.
	 *
	 * @return bool whether the lock was engaged successfully.
	 */
	protected function engageClientLock( string $lock ): bool {
		return $this->lockManager->lock( [ $lock ] )->isOK();
	}

	/**
	 * @see SqlChangeDispatchCoordinator::releaseClient()
	 *
	 * @param IDatabase $db The database connection to work on.
	 * @param string $lock The name of the lock to release.
	 *
	 * @return bool whether the lock was released successfully.
	 */
	protected function releaseClientLock( IDatabase $db, string $lock ): bool {
		return $this->lockManager->unlock( [ $lock ] )->isOK();
	}

	/**
	 * @see SqlChangeDispatchCoordinator::isClientLockUsed()
	 *
	 * @param IDatabase $db The database connection to work on.
	 * @param string $lock The name of the lock to check.
	 *
	 * @return bool false since it's not needed in LockManager-based coordinators
	 */
	protected function isClientLockUsed( IDatabase $db, string $lock ): bool {
		// Not needed
		return false;
	}

}
