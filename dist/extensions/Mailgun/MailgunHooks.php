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
	 * Implements the AlternateUserMailer hook.
	 *
	 * @param array $headers Associative array of headers
	 * @param array $to Array of MailAddress recipients
	 * @param MailAddress $from Single MailAddress sender
	 * @param string $subject Subject line
	 * @param string $body Body text
	 * @return bool|null False to stop other mailers, null to fall back
	 */
	public static function onAlternateUserMailer(
		array $headers,
		array $to,
		MailAddress $from,
		string $subject,
		string $body
	) {
		try {
			wfDebugLog( 'mailgun', 'Entered onAlternateUserMailer' );

			$conf     = RequestContext::getMain()->getConfig();
			$apiKey   = $conf->get( 'MailgunAPIKey' );
			$domain   = $conf->get( 'MailgunDomain' );
			$endpoint = $conf->get( 'MailgunEndpoint', '' );

			if ( $apiKey === '' || $domain === '' ) {
				wfDebugLog( 'mailgun', 'Missing API key or domain' );
				return null;
			}

			// Configure Mailgun SDK
			$configurator = new \Mailgun\HttpClient\HttpClientConfigurator();
			$configurator->setApiKey( $apiKey );
			if ( $endpoint !== '' ) {
				$configurator->setEndpoint( $endpoint );
			}
			$mg = new \Mailgun\Mailgun( $configurator );

			// Build recipients list using __toString()
			$toHeader = implode( ', ', array_map(
				static fn ( MailAddress $addr ) => (string)$addr,
				$to
			) );

			// Build Mailgun parameters
			$params = [
				'from'    => (string)$from,
				'to'      => $toHeader,
				'subject' => $subject,
				'text'    => $body,
			];

			// Add custom headers
			foreach ( $headers as $name => $value ) {
				$params["h:{$name}"] = $value;
			}

			wfDebugLog( 'mailgun', 'Sending via Mailgun: ' . json_encode( $params ) );
			$response = $mg->messages()->send( $domain, $params );

			wfDebugLog( 'mailgun', 'Mailgun API response ID: ' . $response->getId() );
			if ( $response->getId() ) {
				wfDebugLog( 'mailgun', 'Email sent successfully via Mailgun' );
				// handled, stop other mailers
				return false;
			}

			wfDebugLog( 'mailgun', 'Mailgun did not return an ID' );

		} catch ( \Exception $e ) {
			wfDebugLog( 'mailgun', 'Exception in Mailgun send: ' . $e->getMessage() );
		}
	}
}
