<?php

namespace MediaWiki\Extension\WikibaseManifest;

use Config;
use ExtensionRegistry;
use MediaWiki\SpecialPage\SpecialPageFactory;

class OAuthUrlFactory {

	private const OAUTH_EXT_NAME = 'OAuth';
	private const OAUTH_PAGE_NAME = 'OAuthConsumerRegistration';
	private Config $config;
	private $registry;
	private $specialPageFactory;

	public function __construct(
		Config $config,
		ExtensionRegistry $registry,
		SpecialPageFactory $specialPageFactory
	) {
		$this->config = $config;
		$this->registry = $registry;
		$this->specialPageFactory = $specialPageFactory;
	}

	public function getOAuthUrl(): OAuthUrl {
		if ( $this->registry->isLoaded( self::OAUTH_EXT_NAME ) ) {
			$specialPage = $this->specialPageFactory->getPage( self::OAUTH_PAGE_NAME );
			return new SpecialPageOAuthUrl(
				$this->config,
				$specialPage
			);
		}
		return new NullOAuthUrl();
	}
}
