<?php

namespace MediaWiki\Extension\WikibaseManifest;

use MediaWiki\Extensions\OAuth\Backend\Utils;
use MediaWiki\Extensions\OAuth\Frontend\SpecialPages\SpecialMWOAuthConsumerRegistration;
use WikiMap;

class SpecialPageOAuthUrl implements OAuthUrl {

	private $specialPage;

	public function __construct( SpecialMWOAuthConsumerRegistration $specialPage = null ) {
		$this->specialPage = $specialPage;
	}

	public function getValue(): string {
		global $wgMWOAuthCentralWiki;
		if ( Utils::isCentralWiki() ) {
				$url = $this->specialPage->getPageTitle()->getFullURL();
		} else {
				$url = WikiMap::getForeignURL(
					$wgMWOAuthCentralWiki,
					'Special:OAuthConsumerRegistration'
				);
		}
		return $url;
	}
}
