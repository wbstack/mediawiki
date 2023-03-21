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

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\StopForumSpam;

use Html;
use MediaWiki\Block\DatabaseBlock;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterBuilderHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterComputeVariableHook;
use MediaWiki\Extension\AbuseFilter\Hooks\AbuseFilterGenerateUserVarsHook;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Hook\OtherBlockLogLinkHook;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Permissions\Hook\GetUserPermissionsErrorsExpensiveHook;
use RecentChange;
use RequestContext;
use Title;
use User;
use Wikimedia\IPUtils;

class Hooks implements
	AbuseFilterBuilderHook,
	AbuseFilterComputeVariableHook,
	AbuseFilterGenerateUserVarsHook,
	GetUserPermissionsErrorsExpensiveHook,
	OtherBlockLogLinkHook
{

	/**
	 * Computes the sfs-blocked variable
	 * @param string $method
	 * @param VariableHolder $vars
	 * @param array $parameters
	 * @param ?string &$result
	 * @return bool
	 */
	public function onAbuseFilter_computeVariable(
		string $method, VariableHolder $vars, array $parameters, ?string &$result
	) {
		if ( $method === 'sfs-blocked' ) {
			$ip = self::getIPFromUser( $parameters['user'] );
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
	public function onAbuseFilter_builder( &$builderValues ) {
		global $wgSFSIPListLocation;

		if ( $wgSFSIPListLocation ) {
			// Uses: 'abusefilter-edit-builder-vars-sfs-blocked'
			$builderValues['vars']['sfs_blocked'] = 'sfs-blocked';
		}

		return true;
	}

	/**
	 * Get an IP address for a User if possible
	 *
	 * @param User $user
	 * @return bool|string IP address or false
	 */
	private static function getIPFromUser( User $user ) {
		if ( $user->isAnon() ) {
			return $user->getName();
		}

		$context = RequestContext::getMain();
		if ( $context->getUser()->getName() === $user->getName() ) {
			// Only use the main context if the users are the same
			return $context->getRequest()->getIP();
		}

		// Couldn't figure out an IP address
		return false;
	}

	/**
	 * If an IP address is deny-listed, don't let them edit.
	 *
	 * @param Title $title Title being acted upon
	 * @param User $user User performing the action
	 * @param string $action Action being performed
	 * @param array &$result Will be filled with block status if blocked
	 * @return bool
	 */
	public function onGetUserPermissionsErrorsExpensive( $title, $user, $action, &$result ) {
		global $wgSFSIPListLocation, $wgBlockAllowsUTEdit, $wgSFSReportOnly;

		if ( !$wgSFSIPListLocation ) {
			// Not configured
			return true;
		}
		if ( $action === 'read' ) {
			return true;
		}
		if ( $wgBlockAllowsUTEdit && $title->equals( $user->getTalkPage() ) ) {
			// Let a user edit their talk page
			return true;
		}

		$logger = LoggerFactory::getInstance( 'StopForumSpam' );
		$ip = self::getIPFromUser( $user );

		// attempt to get ip from user
		if ( $ip === false ) {
			$logger->info(
				"Unable to obtain IP information for {user}.",
				[ 'user' => $user->getName() ]
			);
			return true;
		}

		// allow if user has sfsblock-bypass
		if ( $user->isAllowed( 'sfsblock-bypass' ) ) {
			$logger->info(
				"{user} is exempt from SFS blocks.",
				[
					'clientip' => $ip,
					'reportonly' => $wgSFSReportOnly,
					'user' => $user->getName()
				]
			);
			return true;
		}

		// allow if user is exempted from autoblocks (borrowed from TorBlock)
		if ( DatabaseBlock::isExemptedFromAutoblocks( $ip ) ) {
			$logger->info(
				"{clientip} is in autoblock exemption list. Exempting from SFS blocks.",
				[ 'clientip' => $ip, 'reportonly' => $wgSFSReportOnly ]
			);
			return true;
		}

		// log "tripped" action and never block in report-only mode
		if ( $wgSFSReportOnly ) {
			$logger->info(
				"Report Only: {user} tripped SFS deny list doing {action} "
				. "by using {clientip} on \"{title}\".",
				[
					'action' => $action,
					'clientip' => $ip,
					'title' => $title->getPrefixedText(),
					'user' => $user->getName()
				]
			);
			return true;
		}

		// ip NOT in SFS deny list
		$denyListManager = DenyListManager::singleton();
		if ( !$denyListManager->isIpDenyListed( $ip ) ) {
			return true;
		}

		// log action when blocked, return error msg
		$logger->info(
			"{user} was blocked by SFS from doing {action} "
			. "by using {clientip} on \"{title}\".",
			[
				'action' => $action,
				'clientip' => $ip,
				'title' => $title->getPrefixedText(),
				'user' => $user->getName()
			]
		);

		// default: set error msg result and return false
		$result = [ 'stopforumspam-blocked', $ip ];
		return false;
	}

	/**
	 * @param array &$msg
	 * @param string $ip
	 * @return bool
	 */
	public function onOtherBlockLogLink( &$msg, $ip ) {
		global $wgSFSIPListLocation;

		if ( !$wgSFSIPListLocation ) {
			return true;
		}

		$denyListManager = DenyListManager::singleton();
		if ( IPUtils::isIPAddress( $ip ) && $denyListManager->isIpDenyListed( $ip ) ) {
			$msg[] = Html::rawElement(
				'span',
				[ 'class' => 'mw-stopforumspam-denylisted' ],
				wfMessage( 'stopforumspam-is-blocked', $ip )->parse()
			);
		}

		return true;
	}
}
