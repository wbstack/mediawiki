<?php

namespace MediaWiki\Extension\WikibaseManifest;

use Config;
use MediaWiki\Extension\OAuth\Backend\Utils;
use MediaWiki\Extension\OAuth\Frontend\SpecialPages\SpecialMWOAuthConsumerRegistration;
use WikiMap;

class SpecialPageOAuthUrl implements OAuthUrl {

	private Config $config;
	private $specialPage;

	public function __construct(
		Config $config,
		?SpecialMWOAuthConsumerRegistration $specialPage = null
	) {
		$this->config = $config;
		$this->specialPage = $specialPage;
	}

	public function getValue(): string {
		if ( Utils::isCentralWiki() ) {
				$url = $this->specialPage->getPageTitle()->getFullURL();
		} else {
				$url = WikiMap::getForeignURL(
					$this->config->get( 'MWOAuthCentralWiki' ),
					'Special:OAuthConsumerRegistration'
				);
		}
		return $url;
	}
}
