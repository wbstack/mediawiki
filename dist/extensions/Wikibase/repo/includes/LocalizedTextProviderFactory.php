<?php

declare( strict_types=1 );

namespace Wikibase\Repo;

use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageFactory;
use Wikibase\View\LocalizedTextProvider;

/**
 * @license GPL-2.0-or-later
 */
class LocalizedTextProviderFactory {
	/**
	 * @var LanguageFactory
	 */
	private $languageFactory;

	/**
	 * @param LanguageFactory $languageFactory
	 */
	public function __construct(
		LanguageFactory $languageFactory
	) {
		$this->languageFactory = $languageFactory;
	}

	public function getForLanguage( Language $language ): LocalizedTextProvider {
		return new MediaWikiLocalizedTextProvider( $language );
	}

	public function getForLanguageCode( string $langCode ): LocalizedTextProvider {
		$language = $this->languageFactory->getLanguage( $langCode );
		return $this->getForLanguage( $language );
	}
}
