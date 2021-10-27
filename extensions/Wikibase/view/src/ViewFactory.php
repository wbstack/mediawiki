<?php

namespace Wikibase\View;

use InvalidArgumentException;
use Language;
use SiteLookup;
use ValueFormatters\NumberLocalizer;
use Wikibase\DataModel\Services\Statement\Grouper\StatementGrouper;
use Wikibase\Lib\DataTypeFactory;
use Wikibase\Lib\Formatters\SnakFormat;
use Wikibase\Lib\Formatters\SnakFormatter;
use Wikibase\Lib\LanguageNameLookup;
use Wikibase\Lib\Store\PropertyOrderProvider;
use Wikibase\Lib\TermLanguageFallbackChain;
use Wikibase\View\Template\TemplateFactory;

/**
 * This is a basic factory to create views for DataModel objects. It contains all dependencies of
 * the views besides request-specific options. Those are required in the parameters.
 *
 * @license GPL-2.0-or-later
 * @author Katie Filbert < aude.wiki@gmail.com >
 * @author Thiemo Kreuz
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class ViewFactory {

	/**
	 * @var HtmlSnakFormatterFactory
	 */
	private $htmlSnakFormatterFactory;

	/**
	 * @var EntityIdFormatterFactory
	 */
	private $htmlIdFormatterFactory;

	/**
	 * @var EntityIdFormatterFactory
	 */
	private $plainTextIdFormatterFactory;

	/**
	 * @var StatementGrouper
	 */
	private $statementGrouper;

	/**
	 * @var PropertyOrderProvider
	 */
	private $propertyOrderProvider;

	/**
	 * @var SiteLookup
	 */
	private $siteLookup;

	/**
	 * @var DataTypeFactory
	 */
	private $dataTypeFactory;

	/**
	 * @var TemplateFactory
	 */
	private $templateFactory;

	/**
	 * @var LanguageNameLookup
	 */
	private $languageNameLookup;

	/**
	 * @var LanguageDirectionalityLookup
	 */
	private $languageDirectionalityLookup;

	/**
	 * @var NumberLocalizer
	 */
	private $numberLocalizer;

	/**
	 * @var string[]
	 */
	private $siteLinkGroups;

	/**
	 * @var string[]
	 */
	private $specialSiteLinkGroups;

	/**
	 * @var string[]
	 */
	private $badgeItems;

	/**
	 * @var LocalizedTextProvider
	 */
	private $textProvider;

	/**
	 * @var SpecialPageLinker
	 */
	private $specialPageLinker;

	/**
	 * @param EntityIdFormatterFactory $htmlIdFormatterFactory
	 * @param EntityIdFormatterFactory $plainTextIdFormatterFactory
	 * @param HtmlSnakFormatterFactory $htmlSnakFormatterFactory
	 * @param StatementGrouper $statementGrouper
	 * @param PropertyOrderProvider $propertyOrderProvider
	 * @param SiteLookup $siteLookup
	 * @param DataTypeFactory $dataTypeFactory
	 * @param TemplateFactory $templateFactory
	 * @param LanguageNameLookup $languageNameLookup
	 * @param LanguageDirectionalityLookup $languageDirectionalityLookup
	 * @param NumberLocalizer $numberLocalizer
	 * @param string[] $siteLinkGroups
	 * @param string[] $specialSiteLinkGroups
	 * @param string[] $badgeItems
	 * @param LocalizedTextProvider $textProvider
	 * @param SpecialPageLinker $specialPageLinker
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		EntityIdFormatterFactory $htmlIdFormatterFactory,
		EntityIdFormatterFactory $plainTextIdFormatterFactory,
		HtmlSnakFormatterFactory $htmlSnakFormatterFactory,
		StatementGrouper $statementGrouper,
		PropertyOrderProvider $propertyOrderProvider,
		SiteLookup $siteLookup,
		DataTypeFactory $dataTypeFactory,
		TemplateFactory $templateFactory,
		LanguageNameLookup $languageNameLookup,
		LanguageDirectionalityLookup $languageDirectionalityLookup,
		NumberLocalizer $numberLocalizer,
		array $siteLinkGroups,
		array $specialSiteLinkGroups,
		array $badgeItems,
		LocalizedTextProvider $textProvider,
		SpecialPageLinker $specialPageLinker
	) {
		if ( !$this->hasValidOutputFormat( $htmlIdFormatterFactory, 'text/html' )
			|| !$this->hasValidOutputFormat( $plainTextIdFormatterFactory, 'text/plain' )
		) {
			throw new InvalidArgumentException( 'Expected an HTML and a plain text EntityIdFormatter factory' );
		}

		$this->htmlIdFormatterFactory = $htmlIdFormatterFactory;
		$this->plainTextIdFormatterFactory = $plainTextIdFormatterFactory;
		$this->htmlSnakFormatterFactory = $htmlSnakFormatterFactory;
		$this->statementGrouper = $statementGrouper;
		$this->propertyOrderProvider = $propertyOrderProvider;
		$this->siteLookup = $siteLookup;
		$this->dataTypeFactory = $dataTypeFactory;
		$this->templateFactory = $templateFactory;
		$this->languageNameLookup = $languageNameLookup;
		$this->languageDirectionalityLookup = $languageDirectionalityLookup;
		$this->numberLocalizer = $numberLocalizer;
		$this->siteLinkGroups = $siteLinkGroups;
		$this->specialSiteLinkGroups = $specialSiteLinkGroups;
		$this->badgeItems = $badgeItems;
		$this->textProvider = $textProvider;
		$this->specialPageLinker = $specialPageLinker;
	}

	/**
	 * @param EntityIdFormatterFactory $factory
	 * @param string $expected
	 *
	 * @return bool
	 */
	private function hasValidOutputFormat( EntityIdFormatterFactory $factory, $expected ) {
		$snakFormat = new SnakFormat();
		switch ( $snakFormat->getBaseFormat( $factory->getOutputFormat() ) ) {
			case SnakFormatter::FORMAT_PLAIN:
				return $expected === 'text/plain';
			case SnakFormatter::FORMAT_HTML:
				return $expected === 'text/html';
		}

		return false;
	}

	/**
	 * Creates an ItemView suitable for rendering the item.
	 *
	 * @param Language $language
	 * @param TermLanguageFallbackChain $termFallbackChain
	 * @param CacheableEntityTermsView $entityTermsView
	 *
	 * @return ItemView
	 * @throws \MWException
	 */
	public function newItemView(
		Language $language,
		TermLanguageFallbackChain $termFallbackChain,
		CacheableEntityTermsView $entityTermsView
	) {
		$editSectionGenerator = $this->newToolbarEditSectionGenerator();

		$statementSectionsView = $this->newStatementSectionsView(
			$language->getCode(),
			$termFallbackChain,
			$editSectionGenerator
		);

		$siteLinksView = new SiteLinksView(
			$this->templateFactory,
			$this->siteLookup->getSites(),
			$editSectionGenerator,
			$this->plainTextIdFormatterFactory->getEntityIdFormatter( $language ),
			$this->languageNameLookup,
			$this->numberLocalizer,
			$this->badgeItems,
			$this->specialSiteLinkGroups,
			$this->textProvider
		);

		return new ItemView(
			$this->templateFactory,
			$entityTermsView,
			$this->languageDirectionalityLookup,
			$statementSectionsView,
			$language->getCode(),
			$siteLinksView,
			$this->siteLinkGroups,
			$this->textProvider
		);
	}

	/**
	 * Creates an PropertyView suitable for rendering the property.
	 *
	 * @param Language $language
	 * @param TermLanguageFallbackChain $termFallbackChain
	 * @param CacheableEntityTermsView $entityTermsView
	 *
	 * @return PropertyView
	 * @throws \MWException
	 */
	public function newPropertyView(
		Language $language,
		TermLanguageFallbackChain $termFallbackChain,
		CacheableEntityTermsView $entityTermsView
	) {
		$statementSectionsView = $this->newStatementSectionsView(
			$language->getCode(),
			$termFallbackChain,
			$this->newToolbarEditSectionGenerator()
		);

		return new PropertyView(
			$this->templateFactory,
			$entityTermsView,
			$this->languageDirectionalityLookup,
			$statementSectionsView,
			$this->dataTypeFactory,
			$language->getCode(),
			$this->textProvider
		);
	}

	/**
	 * @param string $languageCode
	 * @param TermLanguageFallbackChain $termFallbackChain
	 * @param EditSectionGenerator $editSectionGenerator
	 *
	 * @return StatementSectionsView
	 */
	public function newStatementSectionsView(
		$languageCode,
		TermLanguageFallbackChain $termFallbackChain,
		EditSectionGenerator $editSectionGenerator
	) {
		$statementGroupListView = $this->newStatementGroupListView(
			$languageCode,
			$termFallbackChain,
			$editSectionGenerator
		);

		return new StatementSectionsView(
			$this->templateFactory,
			$this->statementGrouper,
			$statementGroupListView,
			$this->textProvider
		);
	}

	/**
	 * @param string $languageCode
	 * @param TermLanguageFallbackChain $termFallbackChain
	 * @param EditSectionGenerator $editSectionGenerator
	 *
	 * @return StatementGroupListView
	 */
	public function newStatementGroupListView(
		$languageCode,
		TermLanguageFallbackChain $termFallbackChain,
		EditSectionGenerator $editSectionGenerator
	) {
		$snakFormatter = $this->htmlSnakFormatterFactory->getSnakFormatter(
			$languageCode,
			$termFallbackChain
		);
		$propertyIdFormatter = $this->htmlIdFormatterFactory->getEntityIdFormatter(
			Language::factory( $languageCode )
		);
		$snakHtmlGenerator = new SnakHtmlGenerator(
			$this->templateFactory,
			$snakFormatter,
			$propertyIdFormatter,
			$this->textProvider
		);
		$statementHtmlGenerator = new StatementHtmlGenerator(
			$this->templateFactory,
			$snakHtmlGenerator,
			$this->numberLocalizer,
			$this->textProvider
		);

		return new StatementGroupListView(
			$this->propertyOrderProvider,
			$this->templateFactory,
			$propertyIdFormatter,
			$editSectionGenerator,
			$statementHtmlGenerator
		);
	}

	private function newToolbarEditSectionGenerator(): ToolbarEditSectionGenerator {
		return new ToolbarEditSectionGenerator(
			$this->specialPageLinker,
			$this->templateFactory,
			$this->textProvider
		);
	}

}
