<?php

declare( strict_types = 1 );

namespace EntitySchema\DataAccess;

use MediaWiki\Page\PageIdentity;
use Wikibase\DataModel\Term\TermFallback;
use Wikibase\Lib\LanguageFallbackChainFactory;

/**
 * Lookup for EntitySchema labels, with language fallbacks applied.
 *
 * @license GPL-2.0-or-later
 */
class LabelLookup {

	private FullViewSchemaDataLookup $fullViewSchemaDataLookup;

	private LanguageFallbackChainFactory $languageFallbackChainFactory;

	public function __construct(
		FullViewSchemaDataLookup $fullViewSchemaDataLookup,
		LanguageFallbackChainFactory $languageFallbackChainFactory
	) {
		$this->fullViewSchemaDataLookup = $fullViewSchemaDataLookup;
		$this->languageFallbackChainFactory = $languageFallbackChainFactory;
	}

	/**
	 * Look up the label of the EntitySchema with the given title, if any.
	 * Language fallbacks are applied based on the given language code.
	 *
	 * @param PageIdentity $title
	 * @param string $langCode
	 * @return TermFallback|null The label, or null if no label or EntitySchema was found.
	 */
	public function getLabelForTitle( PageIdentity $title, string $langCode ): ?TermFallback {
		$schemaData = $this->fullViewSchemaDataLookup->getFullViewSchemaDataForTitle( $title );
		if ( $schemaData === null ) {
			return null;
		}

		$chain = $this->languageFallbackChainFactory->newFromLanguageCode( $langCode );
		$preferredLabel = $chain->extractPreferredValue( array_map(
			fn ( $nameBadge ) => $nameBadge->label,
			$schemaData->nameBadges
		) );
		if ( $preferredLabel !== null ) {
			return new TermFallback(
				$langCode,
				$preferredLabel['value'],
				$preferredLabel['language'],
				$preferredLabel['source']
			);
		} else {
			return null;
		}
	}
}
