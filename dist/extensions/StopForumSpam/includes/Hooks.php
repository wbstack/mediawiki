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

use Html;
use MediaWiki\Block\DatabaseBlock;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Logger\LoggerFactory;
use RequestContext;
use Title;
use User;
use Wikimedia\IPUtils;

class Hooks {

	/**
	 * Computes the sfs-blocked variable
	 * @param string $method
	 * @param VariableHolder $vars
	 * @param array $parameters
	 * @param null &$result
	 * @return bool
	 */
	public static function abuseFilterComputeVariable( $method, $vars, $parameters, &$result ) {
		if ( $method == 'sfs-blocked' ) {
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
	 * @return bool
	 */
	public static function abuseFilterGenerateUserVars( $vars, $user ) {
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
	public static function abuseFilterBuilder( &$builderValues ) {
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
	 * If an IP address is denylisted, don't let them edit.
	 *
	 * @param Title &$title Title being acted upon
	 * @param User &$user User performing the action
	 * @param string $action Action being performed
	 * @param array &$result Will be filled with block status if blocked
	 * @return bool
	 */
	public static function onGetUserPermissionsErrorsExpensive( &$title, &$user, $action, &$result ) {
		global $wgSFSIPListLocation, $wgBlockAllowsUTEdit, $wgSFSReportOnly;

		if ( !$wgSFSIPListLocation ) {
			// Not configured
			return true;
		}
		if ( $action === 'read' ) {
			return true;
		}

		$ip = self::getIPFromUser( $user );
		if ( $ip === false ) {
			return true;
		}

		if ( $wgBlockAllowsUTEdit && $title->equals( $user->getTalkPage() ) ) {
			// Let a user edit their talk page
			return true;
		}

		$denyListManager = DenyListManager::singleton();
		if ( $denyListManager->isIpDenyListed( $ip ) ) {
			$logger = LoggerFactory::getInstance( 'StopForumSpam' );

			$logger->info(
				"{user} tripped SFS deny list doing {action} "
				. "by using {clientip} on \"{title}\".",
				[
					'action' => $action,
					'clientip' => $ip,
					'reportonly' => $wgSFSReportOnly,
					'title' => $title->getPrefixedText(),
					'user' => $user->getName()
				]
			);
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
			// I just copied this from TorBlock, not sure if it actually makes sense.
			if ( DatabaseBlock::isExemptedFromAutoblocks( $ip ) ) {
				$logger->info(
					"{clientip} is in autoblock exemption list. Exempting from SFS blocks.",
					[ 'clientip' => $ip, 'reportonly' => $wgSFSReportOnly ]
				);

				return true;
			}

			// never block in report-only mode
			if ( $wgSFSReportOnly ) {
				return true;
			}

			// log info when action blocked
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

			$result = [ 'stopforumspam-blocked', $ip ];

			return false;
		}

		return true;
	}

	/**
	 * @param array &$msg
	 * @param string $ip
	 * @return bool
	 */
	public static function onOtherBlockLogLink( &$msg, $ip ) {
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
