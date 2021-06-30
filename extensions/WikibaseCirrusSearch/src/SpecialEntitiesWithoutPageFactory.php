<?php

namespace Wikibase\Search\Elastic;

use Wikibase\DataModel\Term\DescriptionsProvider;
use Wikibase\DataModel\Term\LabelsProvider;
use Wikibase\Lib\ContentLanguages;
use Wikibase\Lib\EntityFactory;
use Wikibase\Lib\LanguageNameLookup;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikibase\Lib\TermIndexEntry;
use Wikibase\Repo\WikibaseRepo;

/**
 * Factory to create special pages.
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class SpecialEntitiesWithoutPageFactory {

	private static function newFromGlobalState(): self {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();

		return new self(
			$wikibaseRepo->getLocalEntityTypes(),
			$wikibaseRepo->getTermsLanguages(),
			new LanguageNameLookup(),
			$wikibaseRepo->getEntityFactory(),
			$wikibaseRepo->getEntityNamespaceLookup()
		);
	}

	public static function newSpecialEntitiesWithoutLabel(): SpecialEntitiesWithoutPage {
		return self::newFromGlobalState()->createSpecialEntitiesWithoutLabel();
	}

	public static function newSpecialEntitiesWithoutDescription(): SpecialEntitiesWithoutPage {
		return self::newFromGlobalState()->createSpecialEntitiesWithoutDescription();
	}

	private $entityTypes;
	private $termsLanguages;
	private $languageNameLookup;
	private $entityFactory;
	private $entityNamespaceLookup;

	/**
	 * @param string[] $entityTypes
	 * @param ContentLanguages $termsLanguages
	 * @param LanguageNameLookup $languageNameLookup
	 * @param EntityFactory $entityFactory
	 * @param EntityNamespaceLookup $entityNamespaceLookup
	 */
	public function __construct(
		array $entityTypes,
		ContentLanguages $termsLanguages,
		LanguageNameLookup $languageNameLookup,
		EntityFactory $entityFactory,
		EntityNamespaceLookup $entityNamespaceLookup
	) {
		$this->entityTypes = $entityTypes;
		$this->termsLanguages = $termsLanguages;
		$this->languageNameLookup = $languageNameLookup;
		$this->entityFactory = $entityFactory;
		$this->entityNamespaceLookup = $entityNamespaceLookup;
	}

	public function createSpecialEntitiesWithoutLabel(): SpecialEntitiesWithoutPage {
		$supportedEntityTypes = [];
		foreach ( $this->entityTypes as $entityType ) {
			if ( $this->entityFactory->newEmpty( $entityType ) instanceof LabelsProvider ) {
				$supportedEntityTypes[] = $entityType;
			}
		}
		return new SpecialEntitiesWithoutPage(
			'EntitiesWithoutLabel',
			TermIndexEntry::TYPE_LABEL,
			'wikibasecirrus-entitieswithoutlabel-legend',
			$supportedEntityTypes,
			$this->termsLanguages,
			$this->languageNameLookup,
			$this->entityNamespaceLookup
		);
	}

	public function createSpecialEntitiesWithoutDescription(): SpecialEntitiesWithoutPage {
		$supportedEntityTypes = [];
		foreach ( $this->entityTypes as $entityType ) {
			if ( $this->entityFactory->newEmpty( $entityType ) instanceof DescriptionsProvider ) {
				$supportedEntityTypes[] = $entityType;
			}
		}
		return new SpecialEntitiesWithoutPage(
			'EntitiesWithoutDescription',
			TermIndexEntry::TYPE_DESCRIPTION,
			'wikibasecirrus-entitieswithoutdescription-legend',
			$supportedEntityTypes,
			$this->termsLanguages,
			$this->languageNameLookup,
			$this->entityNamespaceLookup
		);
	}

}
