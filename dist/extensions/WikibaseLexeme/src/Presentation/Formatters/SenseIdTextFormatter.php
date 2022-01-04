<?php

namespace Wikibase\Lexeme\Presentation\Formatters;

use OutOfBoundsException;
use OutOfRangeException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Services\EntityId\EntityIdFormatter;
use Wikibase\Lexeme\Domain\Model\Lexeme;
use Wikibase\Lexeme\Domain\Model\SenseId;
use Wikibase\Lib\Store\EntityRevisionLookup;
use Wikibase\Lib\Store\RevisionedUnresolvedRedirectException;
use Wikibase\Lib\Store\StorageException;
use Wikibase\View\LocalizedTextProvider;

/**
 * @license GPL-2.0-or-later
 */
class SenseIdTextFormatter implements EntityIdFormatter {

	/**
	 * @var EntityRevisionLookup
	 */
	private $revisionLookup;

	/**
	 * @var LocalizedTextProvider
	 */
	private $localizedTextProvider;

	public function __construct(
		EntityRevisionLookup $revisionLookup,
		LocalizedTextProvider $localizedTextProvider
	) {
		$this->revisionLookup = $revisionLookup;
		$this->localizedTextProvider = $localizedTextProvider;
	}

	/**
	 * @param SenseId $value
	 *
	 * @return string plain text
	 */
	public function formatEntityId( EntityId $value ) {
		try {
			$lexemeRevision = $this->revisionLookup->getEntityRevision( $value->getLexemeId() );
		} catch ( RevisionedUnresolvedRedirectException | StorageException $e ) {
			$lexemeRevision = null; // see fallback below
		}

		if ( $lexemeRevision === null ) {
			return $value->getSerialization();
		}

		/** @var Lexeme $lexeme */
		$lexeme = $lexemeRevision->getEntity();
		'@phan-var Lexeme $lexeme';
		try {
			$sense = $lexeme->getSense( $value );
		} catch ( OutOfRangeException $e ) {
			return $value->getSerialization();
		}

		$lemmas = implode(
			$this->localizedTextProvider->get(
				'wikibaselexeme-presentation-lexeme-display-label-separator-multiple-lemma'
			),
			$lexeme->getLemmas()->toTextArray()
		);

		$messageKey = 'wikibaselexeme-senseidformatter-layout';
		$languageCode = $this->localizedTextProvider->getLanguageOf( $messageKey );
		try {
			// TODO language fallbacks (T200983)
			$gloss = $sense->getGlosses()->getByLanguage( $languageCode )->getText();
		} catch ( OutOfBoundsException $e ) {
			return $value->getSerialization();
		}

		return $this->localizedTextProvider->get(
			$messageKey,
			[ $lemmas, $gloss ]
		);
	}

}
