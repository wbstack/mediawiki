<?php

namespace MediaWiki\Extension\WikibaseManifest;

use ExtensionRegistry;
use MediaWiki\Special\SpecialPageFactory;

class OAuthUrlFactory {

	private const OAUTH_EXT_NAME = 'OAuth';
	private const OAUTH_PAGE_NAME = 'OAuthConsumerRegistration';
	private $registry;
	private $specialPageFactory;

	public function __construct(
		ExtensionRegistry $registry,
		SpecialPageFactory $specialPageFactory
	) {
		$this->registry = $registry;
		$this->specialPageFactory = $specialPageFactory;
	}

	public function getOAuthUrl(): OAuthUrl {
		if ( $this->registry->isLoaded( self::OAUTH_EXT_NAME ) ) {
			$specialPage = $this->specialPageFactory->getPage( self::OAUTH_PAGE_NAME );
			return new SpecialPageOAuthUrl( $specialPage );
		}
		return new NullOAuthUrl();
	}
}
