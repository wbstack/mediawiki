<?php

namespace Wikibase\Lib;

use Html;
use Wikibase\DataModel\Term\TermFallback;

/**
 * Generates HTML (usually a 'sup' element) to make the actual and source languages of terms
 * (typically labels and descriptions) that are the result of a language fallback chain and/or
 * transliteration visible to the user.
 *
 * @license GPL-2.0-or-later
 * @author Adrian Heine <adrian.heine@wikimedia.de>
 * @author Thiemo Kreuz
 */
class LanguageFallbackIndicator {

	/**
	 * @var LanguageNameLookup
	 */
	private $languageNameLookup;

	public function __construct( LanguageNameLookup $languageNameLookup ) {
		$this->languageNameLookup = $languageNameLookup;
	}

	public function getHtml( TermFallback $term ) {
		$requestedLanguage = $term->getLanguageCode();
		$actualLanguage = $term->getActualLanguageCode();
		$sourceLanguage = $term->getSourceLanguageCode();

		$isFallback = $actualLanguage !== $requestedLanguage;
		$isTransliteration = $sourceLanguage !== null && $sourceLanguage !== $actualLanguage;

		if ( !$isFallback && !$isTransliteration ) {
			return '';
		}

		$text = $this->languageNameLookup->getName( $actualLanguage );

		if ( $isTransliteration ) {
			$text = wfMessage(
				'wikibase-language-fallback-transliteration-hint',
				$this->languageNameLookup->getName( $sourceLanguage ),
				$text
			)->text();
		}

		$classes = 'wb-language-fallback-indicator';
		if ( $isTransliteration ) {
			$classes .= ' wb-language-fallback-transliteration';
		}
		if ( $isFallback
			&& $this->getBaseLanguage( $actualLanguage ) === $this->getBaseLanguage( $requestedLanguage )
		) {
			$classes .= ' wb-language-fallback-variant';
		}
		if ( $isFallback && $actualLanguage === 'mul' ) {
			$classes .= ' wb-language-fallback-mul';
		}

		$attributes = [ 'class' => $classes ];

		$html = Html::element( 'sup', $attributes, $text );
		return '&nbsp;' . $html;
	}

	/**
	 * @param string $languageCode
	 *
	 * @return string
	 */
	private function getBaseLanguage( $languageCode ) {
		return preg_replace( '/-.*/', '', $languageCode );
	}

}
