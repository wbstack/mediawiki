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

use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterBuilderHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterComputeVariableHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterGenerateUserVarsHook;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use RecentChange;
use User;

class AbuseFilterHookHandler implements
	AbuseFilterBuilderHook,
	AbuseFilterComputeVariableHook,
	AbuseFilterGenerateUserVarsHook
{

	/**
	 * Computes the sfs-blocked variable
	 * @param string $method
	 * @param VariableHolder $vars
	 * @param array $parameters
	 * @param ?string &$result
	 * @return bool
	 */
	// phpcs:ignore
	public function onAbuseFilter_computeVariable(
		string $method, VariableHolder $vars, array $parameters, ?string &$result
	) {
		if ( $method === 'sfs-blocked' ) {
			$ip = Hooks::getIPFromUser( $parameters['user'] );
			if ( $ip === false ) {
				$result = false;
			} else {
				$result = DenyListManager::singleton()->isIpDenyListed( $ip );
			}

			return false;
		}

		return true;
	}

	/**
	 * Load our blocked variable
	 * @param VariableHolder $vars
	 * @param User $user
	 * @param ?RecentChange $rc
	 * @return bool
	 */
	// phpcs:ignore
	public function onAbuseFilter_generateUserVars( VariableHolder $vars, User $user, ?RecentChange $rc ) {
		global $wgSFSIPListLocation;

		if ( $wgSFSIPListLocation ) {
			$vars->setLazyLoadVar( 'sfs_blocked', 'sfs-blocked', [ 'user' => $user ] );
		}

		return true;
	}

	/**
	 * Tell AbuseFilter about our sfs-blocked variable
	 * @param array &$builderValues
	 * @return bool
	 */
	// phpcs:ignore
	public function onAbuseFilter_builder( &$builderValues ) {
		global $wgSFSIPListLocation;

		if ( $wgSFSIPListLocation ) {
			// Uses: 'abusefilter-edit-builder-vars-sfs-blocked'
			$builderValues['vars']['sfs_blocked'] = 'sfs-blocked';
		}

		return true;
	}
}
