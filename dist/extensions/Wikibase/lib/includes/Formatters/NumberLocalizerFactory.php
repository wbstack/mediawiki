<?php

declare( strict_types=1 );

namespace Wikibase\Lib\Formatters;

use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageFactory;
use ValueFormatters\NumberLocalizer;

/**
 * @license GPL-2.0-or-later
 */
class NumberLocalizerFactory {

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

	public function getForLanguage( Language $language ): NumberLocalizer {
		return new MediaWikiNumberLocalizer( $language );
	}

	public function getForLanguageCode( string $langCode ): NumberLocalizer {
		$language = $this->languageFactory->getLanguage( $langCode );
		return $this->getForLanguage( $language );
	}

}
