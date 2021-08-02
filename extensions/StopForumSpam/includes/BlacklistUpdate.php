<?php
/**
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
 * https://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\StopForumSpam;

use DeferrableUpdate;
use Wikimedia\IPUtils;

class BlacklistUpdate implements DeferrableUpdate {
	private $lineNo, $usedKeys, $data, $skipLines, $finished = false;

	public function doUpdate() {
		global $wgSFSIPListLocation, $wgSFSIPThreshold, $wgSFSValidateIPList,
			$wgSFSBlacklistCacheDuration, $wgMemc;
		if ( $wgSFSIPListLocation === false ) {
			wfDebugLog( 'StopForumSpam', '$wgSFSIPListLocation has not been configured properly.' );

			return;
		}

		// So that we don't start other concurrent updates
		// Have the key expire an hour early so we hopefully don't have a time where there is no blacklist
		$wgMemc->set( BlacklistManager::getBlacklistKey(), 1, $wgSFSBlacklistCacheDuration - 3600 );

		// Grab and then clear any update state
		$state = $wgMemc->get( BlacklistManager::getBlacklistUpdateStateKey() );
		if ( $state !== false ) {
			$wgMemc->delete( BlacklistManager::getBlacklistUpdateStateKey() );
		}

		// For batching purposes, this saves our current progress so we
		// know where to pick up in case we run out of time
		register_shutdown_function( [ $this, 'saveState' ] );

		// Try to keep this running even if the user hits the stop button
		ignore_user_abort( true );

		$this->data = [];
		$this->lineNo = 0;
		$this->usedKeys = [];
		$this->skipLines = 0;
		$this->restoreState( $state );
		$fh = fopen( $wgSFSIPListLocation, 'rb' );

		if ( !$fh ) {
			return;
		}

		// Set up output buffering so we don't accidentally try to send stuff
		ob_start();

		while ( !feof( $fh ) ) {
			$ip = fgetcsv( $fh, 4096, ',', '"' );
			if ( $ip === false ) {
				break;
			}
			$this->lineNo++;
			if ( $this->lineNo < $this->skipLines ) {
				continue;
			} elseif (
				// errors with $fh
				$ip === null ||
				// empty line
				$ip === [ null ] ||
				// @phan-suppress-next-line PhanTypeMismatchArgumentNullable
				( $wgSFSValidateIPList && ( !IPUtils::isValid( $ip[0] ) || IPUtils::isIPv6( $ip[0] ) ) )
			) {
				// discard invalid lines
				continue;
			} elseif ( isset( $ip[1] ) && $ip[1] < $wgSFSIPThreshold ) {
				// wasn't hit enough times
				continue;
			}
			// @phan-suppress-next-line PhanTypeMismatchArgument
			list( $bucket, $offset ) = BlacklistManager::getBucketAndOffset( $ip[0] );
			$key = BlacklistManager::getIPBlacklistKey( $bucket );
			if ( !isset( $this->data[$key] ) ) {
				// @phan-suppress-next-line PhanSuspiciousWeakTypeComparisonInLoop
				if ( in_array( $key, $this->usedKeys ) ) {
					$this->data[$key] = $wgMemc->get( $key );
				} else {
					$this->data[$key] = 0;
				}
			}
			$this->data[$key] |= ( 1 << $offset );
		}

		$this->saveData();

		fclose( $fh );

		// End output buffering
		ob_end_clean();

		$this->finished = true;
	}

	private function saveData() {
		global $wgMemc, $wgSFSBlacklistCacheDuration;
		$wgMemc->setMulti( $this->data, $wgSFSBlacklistCacheDuration );
	}

	/**
	 * Saves the current progress in doUpdate() so we can pick it up at a later request
	 */
	public function saveState() {
		global $wgSFSIPListLocation, $wgMemc;

		if ( $this->finished ) {
			// Yay, we're done!
			return;
		}

		// Save the buckets
		$this->saveData();

		// Save where we left off
		$wgMemc->set(
			BlacklistManager::getBlacklistUpdateStateKey(),
			[
				'skipLines' => $this->lineNo,
				'usedKeys' => array_keys( $this->data ),
				'filemtime' => filemtime( $wgSFSIPListLocation ),
			],
			30 * 86400
		);
		if ( ob_get_level() ) {
			ob_end_clean();
		}
	}

	/**
	 * Restores the state of doUpdate() to when the script exited the previous time
	 *
	 * @param bool|array $state
	 */
	private function restoreState( $state ) {
		global $wgSFSIPListLocation;
		if ( $state === false ) {
			// no state to restore
			return;
		}

		if ( filemtime( $wgSFSIPListLocation ) != $state['filemtime'] ) {
			// file was modified since we last ran, so our state is invalid
			return;
		}
		$this->lineNo = $state['lineNo'];
		if ( !isset( $state['usedKeys'] ) ) {
			// Old code version, invalidate
			return;
		}
		$this->usedKeys = $state['usedKeys'];
	}
}
