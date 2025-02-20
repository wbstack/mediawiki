<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Specials;

use MediaWiki\Message\Message;
use MediaWiki\Output\OutputPage;
use MediaWiki\Site\Site;
use MediaWiki\Status\Status;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Term;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikibase\Lib\Store\FallbackLabelDescriptionLookupFactory;
use Wikibase\Lib\Summary;
use Wikibase\Repo\AnonymousEditWarningBuilder;
use Wikibase\Repo\CopyrightMessageBuilder;
use Wikibase\Repo\EditEntity\MediaWikiEditEntityFactory;
use Wikibase\Repo\SiteLinkPageNormalizer;
use Wikibase\Repo\SiteLinkTargetProvider;
use Wikibase\Repo\Specials\HTMLForm\HTMLAliasesField;
use Wikibase\Repo\Specials\HTMLForm\HTMLContentLanguageField;
use Wikibase\Repo\Specials\HTMLForm\HTMLTrimmedTextField;
use Wikibase\Repo\Store\TermsCollisionDetector;
use Wikibase\Repo\SummaryFormatter;
use Wikibase\Repo\Validators\TermValidatorFactory;
use Wikibase\Repo\Validators\ValidatorErrorLocalizer;

/**
 * Page for creating new Wikibase items.
 *
 * @license GPL-2.0-or-later
 * @author John Erling Blad < jeblad@gmail.com >
 */
class SpecialNewItem extends SpecialNewEntity {

	public const FIELD_LANG = 'lang';
	public const FIELD_LABEL = 'label';
	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_ALIASES = 'aliases';
	public const FIELD_SITE = 'site';
	public const FIELD_PAGE = 'page';
	public const FIELD_BADGES = 'badges';

	private SiteLinkPageNormalizer $siteLinkPageNormalizer;

	private AnonymousEditWarningBuilder $anonymousEditWarningBuilder;

	private TermValidatorFactory $termValidatorFactory;

	private TermsCollisionDetector $termsCollisionDetector;

	private ValidatorErrorLocalizer $errorLocalizer;

	private SiteLinkTargetProvider $siteLinkTargetProvider;

	/**
	 * @var string[]
	 */
	private array $siteLinkGroups;

	private FallbackLabelDescriptionLookupFactory $labelDescriptionLookupFactory;

	/**
	 * @var string[]
	 */
	private array $badgeItems;

	public function __construct(
		array $tags,
		SpecialPageCopyrightView $copyrightView,
		EntityNamespaceLookup $entityNamespaceLookup,
		SummaryFormatter $summaryFormatter,
		EntityTitleLookup $entityTitleLookup,
		MediaWikiEditEntityFactory $editEntityFactory,
		SiteLinkPageNormalizer $siteLinkPageNormalizer,
		AnonymousEditWarningBuilder $anonymousEditWarningBuilder,
		TermValidatorFactory $termValidatorFactory,
		TermsCollisionDetector $termsCollisionDetector,
		ValidatorErrorLocalizer $errorLocalizer,
		SiteLinkTargetProvider $siteLinkTargetProvider,
		FallbackLabelDescriptionLookupFactory $labelDescriptionLookupFactory,
		array $badgeItems,
		array $siteLinkGroups,
		bool $isMobileView
	) {
		parent::__construct(
			'NewItem',
			'createpage',
			$tags,
			$copyrightView,
			$entityNamespaceLookup,
			$summaryFormatter,
			$entityTitleLookup,
			$editEntityFactory,
			$isMobileView
		);
		$this->anonymousEditWarningBuilder = $anonymousEditWarningBuilder;
		$this->termValidatorFactory = $termValidatorFactory;
		$this->termsCollisionDetector = $termsCollisionDetector;
		$this->errorLocalizer = $errorLocalizer;
		$this->siteLinkPageNormalizer = $siteLinkPageNormalizer;
		$this->siteLinkTargetProvider = $siteLinkTargetProvider;
		$this->siteLinkGroups = $siteLinkGroups;
		$this->labelDescriptionLookupFactory = $labelDescriptionLookupFactory;
		$this->badgeItems = $badgeItems;
	}

	public static function factory(
		AnonymousEditWarningBuilder $anonymousEditWarningBuilder,
		MediaWikiEditEntityFactory $editEntityFactory,
		EntityNamespaceLookup $entityNamespaceLookup,
		EntityTitleLookup $entityTitleLookup,
		FallbackLabelDescriptionLookupFactory $labelDescriptionLookupFactory,
		TermsCollisionDetector $itemTermsCollisionDetector,
		bool $isMobileView,
		SettingsArray $repoSettings,
		SiteLinkPageNormalizer $siteLinkPageNormalizer,
		SiteLinkTargetProvider $siteLinkTargetProvider,
		SummaryFormatter $summaryFormatter,
		TermValidatorFactory $termValidatorFactory,
		ValidatorErrorLocalizer $errorLocalizer
	): self {
		$copyrightView = new SpecialPageCopyrightView(
			new CopyrightMessageBuilder(),
			$repoSettings->getSetting( 'dataRightsUrl' ),
			$repoSettings->getSetting( 'dataRightsText' )
		);

		return new self(
			$repoSettings->getSetting( 'specialPageTags' ),
			$copyrightView,
			$entityNamespaceLookup,
			$summaryFormatter,
			$entityTitleLookup,
			$editEntityFactory,
			$siteLinkPageNormalizer,
			$anonymousEditWarningBuilder,
			$termValidatorFactory,
			$itemTermsCollisionDetector,
			$errorLocalizer,
			$siteLinkTargetProvider,
			$labelDescriptionLookupFactory,
			$repoSettings->getSetting( 'badgeItems' ),
			$repoSettings->getSetting( 'siteLinkGroups' ),
			$isMobileView
		);
	}

	/**
	 * @see SpecialNewEntity::doesWrites
	 */
	public function doesWrites(): bool {
		return true;
	}

	/**
	 * @see SpecialNewEntity::createEntityFromFormData
	 */
	protected function createEntityFromFormData( array $formData ): Item {
		$languageCode = $formData[ self::FIELD_LANG ];

		$item = new Item();
		$item->setLabel( $languageCode, $formData[ self::FIELD_LABEL ] );
		$item->setDescription( $languageCode, $formData[ self::FIELD_DESCRIPTION ] );

		$item->setAliases( $languageCode, $formData[ self::FIELD_ALIASES ] );

		if ( isset( $formData[ self::FIELD_SITE ] ) ) {
			$site = $this->getSiteLinkTargetSite( $formData[ self::FIELD_SITE ] );
			'@phan-var Site $site'; // site is guaranteed to exist by this point

			$badges = $formData[ self::FIELD_BADGES ];

			$normalizedPageName = $this->siteLinkPageNormalizer->normalize( $site, $formData[ self::FIELD_PAGE ], $badges );

			$badgeItemIds = array_map(
				fn ( $badgeId ) => new ItemId( $badgeId ),
				$badges
			);

			$item->getSiteLinkList()->addNewSiteLink( $site->getGlobalId(), $normalizedPageName, $badgeItemIds );
		}

		return $item;
	}

	/**
	 * @return array[]
	 */
	protected function getFormFields(): array {
		$formFields = [
			self::FIELD_LANG => [
				'name' => self::FIELD_LANG,
				'class' => HTMLContentLanguageField::class,
				'id' => 'wb-newentity-language',
			],
			self::FIELD_LABEL => [
				'name' => self::FIELD_LABEL,
				'default' => $this->parts[0] ?? '',
				'class' => HTMLTrimmedTextField::class,
				'id' => 'wb-newentity-label',
				'placeholder-message' => 'wikibase-label-edit-placeholder',
				'label-message' => 'wikibase-newentity-label',
			],
			self::FIELD_DESCRIPTION => [
				'name' => self::FIELD_DESCRIPTION,
				'default' => $this->parts[1] ?? '',
				'class' => HTMLTrimmedTextField::class,
				'id' => 'wb-newentity-description',
				'placeholder-message' => 'wikibase-description-edit-placeholder',
				'label-message' => 'wikibase-newentity-description',
			],
			self::FIELD_ALIASES => [
				'name' => self::FIELD_ALIASES,
				'class' => HTMLAliasesField::class,
				'id' => 'wb-newentity-aliases',
			],
		];

		$request = $this->getRequest();
		if ( $request->getCheck( self::FIELD_SITE ) && $request->getCheck( self::FIELD_PAGE ) ) {
			$formFields[ self::FIELD_SITE ] = [
				'name' => self::FIELD_SITE,
				'default' => $request->getVal( self::FIELD_SITE ),
				'type' => 'text',
				'id' => 'wb-newitem-site',
				'readonly' => 'readonly',
				'validation-callback' => function ( $siteId, $formData ) {
					$site = $this->getSiteLinkTargetSite( $siteId );

					if ( $site === null ) {
						return [ $this->msg( 'wikibase-newitem-not-recognized-siteid' )->text() ];
					}

					return true;
				},
				'label-message' => 'wikibase-newitem-site',
			];

			$formFields[ self::FIELD_PAGE ] = [
				'name' => self::FIELD_PAGE,
				'default' => $request->getVal( self::FIELD_PAGE ),
				'type' => 'text',
				'id' => 'wb-newitem-page',
				'readonly' => 'readonly',
				'validation-callback' => function ( $pageName, $formData ) {
					$siteId = $formData['site'];
					$site = $this->getSiteLinkTargetSite( $siteId );
					if ( $site === null ) {
						return true;
					}

					$normalizedPageName = $site->normalizePageName( $pageName );
					if ( $normalizedPageName === false ) {
						return [
							$this->msg(
								'wikibase-newitem-no-external-page',
								$siteId,
								$pageName
							)->text(),
						];
					}

					return true;
				},
				'label-message' => 'wikibase-newitem-page',
			];

			$formFields[ self::FIELD_BADGES ] = [
				'name' => self::FIELD_BADGES,
				'type' => 'multiselect',
				'label-message' => 'wikibase-setsitelink-badges',
				'options' => $this->getMultiSelectOptionsForBadges(),
				'default' => [],
				'id' => 'wb-newentity-badges',
			];
		}

		return $formFields;
	}

	private function getMultiSelectOptionsForBadges(): array {
		$badgesOptions = [];

		/** @var ItemId[] $badgeItemIds */
		$badgeItemIds = array_map(
			fn ( $badgeId ) => new ItemId( $badgeId ),
			array_keys( $this->badgeItems )
		);

		$labelLookup = $this->labelDescriptionLookupFactory->newLabelDescriptionLookup(
			$this->getLanguage(),
			$badgeItemIds
		);

		foreach ( $badgeItemIds as $badgeId ) {
			$idSerialization = $badgeId->getSerialization();

			$label = $labelLookup->getLabel( $badgeId );
			$label = $label === null ? $idSerialization : $label->getText();

			$badgesOptions[$label] = $idSerialization;
		}

		return $badgesOptions;
	}

	protected function getLegend(): Message {
		return $this->msg( 'wikibase-newitem-fieldset' );
	}

	/**
	 * @see SpecialNewEntity::getWarnings
	 *
	 * @return string[]
	 */
	protected function getWarnings(): array {
		if ( !$this->getUser()->isRegistered() ) {
			return [ $this->anonymousEditWarningBuilder->buildAnonymousEditWarningHTML( $this->getFullTitle()->getPrefixedText() ) ];
		}

		return [];
	}

	protected function validateFormData( array $formData ): Status {
		$status = Status::newGood();

		if ( $formData[ self::FIELD_LABEL ] == ''
			 && $formData[ self::FIELD_DESCRIPTION ] == ''
			 && $formData[ self::FIELD_ALIASES ] === []
		) {
			$status->fatal( 'wikibase-newitem-insufficient-data' );
		}

		// Disallow the same label and description, but ignore if both are empty T100933
		if ( $formData[ self::FIELD_LABEL ] !== '' &&
			$formData[ self::FIELD_LABEL ] === $formData[ self::FIELD_DESCRIPTION ]
		) {
			$status->fatal( 'wikibase-newitem-same-label-and-description' );
		}

		if ( $formData[self::FIELD_LABEL] != '' ) {
			$validator = $this->termValidatorFactory->getLabelValidator( $this->getEntityType() );
			$result = $validator->validate( $formData[self::FIELD_LABEL] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );

			$validator = $this->termValidatorFactory->getLabelLanguageValidator();
			$result = $validator->validate( $formData[self::FIELD_LANG] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
		}

		if ( $formData[self::FIELD_DESCRIPTION] != '' ) {
			$validator = $this->termValidatorFactory->getDescriptionValidator();
			$result = $validator->validate( $formData[self::FIELD_DESCRIPTION] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );

			$validator = $this->termValidatorFactory->getDescriptionLanguageValidator();
			$result = $validator->validate( $formData[self::FIELD_LANG] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
		}

		if ( $formData[self::FIELD_ALIASES] !== [] ) {
			$validator = $this->termValidatorFactory->getAliasValidator();
			foreach ( $formData[self::FIELD_ALIASES] as $alias ) {
				$result = $validator->validate( $alias );
				$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
			}

			$result = $validator->validate( implode( '|', $formData[self::FIELD_ALIASES] ) );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );

			$validator = $this->termValidatorFactory->getAliasLanguageValidator();
			$result = $validator->validate( $formData[self::FIELD_LANG] );
			$status->merge( $this->errorLocalizer->getResultStatus( $result ) );
		}

		if ( $status->isOK() ) { // only do this more expensive check if everything else is OK
			$collidingItemId = $this->termsCollisionDetector->detectLabelAndDescriptionCollision(
				$formData[self::FIELD_LANG],
				$formData[self::FIELD_LABEL],
				$formData[self::FIELD_DESCRIPTION]
			);
			if ( $collidingItemId !== null ) {
				$status->fatal(
					'wikibase-validator-label-with-description-conflict',
					$formData[self::FIELD_LABEL],
					$formData[self::FIELD_LANG],
					$collidingItemId
				);
			}
		}

		return $status;
	}

	private function getSiteLinkTargetSite( string $siteId ): ?Site {
		$targetSites = $this->siteLinkTargetProvider->getSiteList( $this->siteLinkGroups );
		if ( !$targetSites->hasSite( $siteId ) ) {
			return null;
		}
		return $targetSites->getSite( $siteId );
	}

	/**
	 * @param Item $item
	 *
	 * @return Summary
	 * @suppress PhanParamSignatureMismatch Uses intersection types
	 */
	protected function createSummary( EntityDocument $item ): Summary {
		$uiLanguageCode = $this->getLanguage()->getCode();

		$summary = new Summary( 'wbeditentity', 'create' );
		$summary->setLanguage( $uiLanguageCode );
		/** @var Term|null $labelTerm */
		$labelTerm = $item->getLabels()->getIterator()->current();
		/** @var Term|null $descriptionTerm */
		$descriptionTerm = $item->getDescriptions()->getIterator()->current();
		$summary->addAutoSummaryArgs(
			$labelTerm ? $labelTerm->getText() : '',
			$descriptionTerm ? $descriptionTerm->getText() : ''
		);

		return $summary;
	}

	protected function displayBeforeForm( OutputPage $output ): void {
		parent::displayBeforeForm( $output );
		$output->addModules( 'wikibase.special.languageLabelDescriptionAliases' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getEntityType(): string {
		return Item::ENTITY_TYPE;
	}

}
