<?php
/**
 * Hooks for Mailgun extension for Mediawiki
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
 * @author Tony Thomas <01tonythomas@gmail.com>
 * @license GPL-2.0-or-later
 * @ingroup Extensions
*/

class MailgunHooks {
	/**
	 * Send a mail using Mailgun API
	 *
	 * @param array $headers
	 * @param array $to
	 * @param MailAddress $from
	 * @param string $subject
	 * @param string $body
	 * @return bool
	 * @throws Exception
	 */
	public static function onAlternateUserMailer(
		array $headers,
		array $to,
		MailAddress $from,
		$subject,
		$body
	) {
		$conf = RequestContext::getMain()->getConfig();

		$mailgunAPIKey = $conf->get( 'MailgunAPIKey' );
		$mailgunDomain = $conf->get( 'MailgunDomain' );
		if ( $mailgunAPIKey == "" || $mailgunDomain == "" ) {
			throw new MWException( "Please update your LocalSettings.php with the correct Mailgun API configurations" );
		}

		$mailgunEndpoint = "";
		if ( $conf->has( 'MailgunEndpoint' ) ) {
			$mailgunEndpoint = $conf->get( 'MailgunEndpoint' );
		}

		$mailgunConfigurator = new \Mailgun\HttpClient\HttpClientConfigurator();
		$mailgunConfigurator->setApiKey( $mailgunAPIKey );

		if ( !empty( $mailgunEndpoint ) ) {
			$mailgunConfigurator->setEndpoint( $mailgunEndpoint );
		}

		$mailgunTransport = new \Mailgun\Mailgun( $mailgunConfigurator );

		return self::sendBatchMessage( $mailgunTransport, $mailgunDomain, $headers, $to, $from, $subject, $body );
	}

	/**
	 * Submit a batch message using Mailgun API
	 *
	 * @param \Mailgun\Mailgun $mailgunTransport
	 * @param string $mailgunDomain
	 * @param array $headers
	 * @param array $to
	 * @param MailAddress $from
	 * @param string $subject
	 * @param string $body
	 * @return false|string
	 * @throws Exception
	 */
	public static function sendBatchMessage(
		\Mailgun\Mailgun $mailgunTransport,
		$mailgunDomain,
		array $headers,
		array $to,
		MailAddress $from,
		$subject,
		$body
	) {
		$message = $mailgunTransport->messages()->getBatchMessage( $mailgunDomain );

		$message->setFromAddress( $from );
		$message->setSubject( $subject );
		$message->setTextBody( $body );

		if ( isset( $headers['Return-Path'] ) ) {
			$message->setReplyToAddress( $headers['Return-Path'] );
		}

		if ( isset( $headers['X-Mailer'] ) ) {
			$message->addCustomHeader( "X-Mailer", $headers['X-Mailer'] );
		}

		if ( isset( $headers['List-Unsubscribe'] ) ) {
			$message->addCustomHeader( "List-Unsubscribe", $headers['List-Unsubscribe'] );
		}

		foreach( $to as $recip ) {
			try {
				$message->addToRecipient( $recip, [] );
			} catch( Exception $e ) {
				return $e->getMessage();
			}
		}

		try {
			$message->finalize();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return false;
	}
}
