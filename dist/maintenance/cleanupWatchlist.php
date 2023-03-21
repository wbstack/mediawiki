<?php
/**
 * Remove broken, unparseable titles in the watchlist table.
 *
 * Usage: php cleanupWatchlist.php [--fix]
 * Options:
 *   --fix  Actually remove entries; without will only report.
 *
 * Copyright © 2005,2006 Brion Vibber <brion@pobox.com>
 * https://www.mediawiki.org/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @author Brion Vibber <brion at pobox.com>
 * @ingroup Maintenance
 */

use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;

require_once __DIR__ . '/TableCleanup.php';

/**
 * Maintenance script to remove broken, unparseable titles in the watchlist table.
 *
 * @ingroup Maintenance
 */
class CleanupWatchlist extends TableCleanup {
	protected $defaultParams = [
		'table' => 'watchlist',
		'index' => [ 'wl_id' ],
		'conds' => [],
		'callback' => 'processRow'
	];

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script to remove broken, unparseable titles in the Watchlist' );
		$this->addOption( 'fix', 'Actually remove entries; without will only report.' );
	}

	public function execute() {
		if ( !$this->hasOption( 'fix' ) ) {
			$this->output( "Dry run only: use --fix to enable updates\n" );
		}
		parent::execute();
	}

	protected function processRow( $row ) {
		$current = Title::makeTitle( $row->wl_namespace, $row->wl_title );
		$display = $current->getPrefixedText();
		$verified = MediaWikiServices::getInstance()->getContentLanguage()->normalize( $display );
		$title = Title::newFromText( $verified );

		if ( $row->wl_user == 0 || $title === null || !$title->equals( $current ) ) {
			$this->output( "invalid watch by {$row->wl_user} for "
				. "({$row->wl_namespace}, \"{$row->wl_title}\")\n" );
			$updated = $this->removeWatch( $row );
			$this->progress( $updated );

			return;
		}
		$this->progress( 0 );
	}

	private function removeWatch( $row ) {
		if ( !$this->dryrun && $this->hasOption( 'fix' ) ) {
			$dbw = $this->getDB( DB_PRIMARY );
			$dbw->delete(
				'watchlist',
				[ 'wl_id' => $row->wl_id ],
				__METHOD__
			);
			if ( $this->getConfig()->get( MainConfigNames::WatchlistExpiry ) ) {
				$dbw->delete(
					'watchlist_expiry',
					[ 'we_item' => $row->wl_id ],
					__METHOD__
				);
			}

			$this->output( "- removed\n" );

			return 1;
		} else {
			return 0;
		}
	}
}

$maintClass = CleanupWatchlist::class;
require_once RUN_MAINTENANCE_IF_MAIN;
