<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\ParserOutput\PlaceholderExpander;

use InvalidArgumentException;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\UserIdentity;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Term\AliasesProvider;
use Wikibase\DataModel\Term\DescriptionsProvider;
use Wikibase\DataModel\Term\LabelsProvider;
use Wikibase\DataModel\Term\TermList;
use Wikibase\Lib\LanguageFallbackChainFactory;
use Wikibase\Lib\LanguageNameLookup;
use Wikibase\View\LanguageDirectionalityLookup;
use Wikibase\View\LocalizedTextProvider;
use Wikibase\View\Template\TemplateFactory;
use Wikibase\View\TermsListView;

/**
 * Utility for expanding placeholders left in the HTML
 *
 * This is used to inject any non-cacheable information into the HTML
 * that was cached as part of the ParserOutput.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 * @author Adrian Heine <adrian.heine@wikimedia.de>
 */
class EntityViewPlaceholderExpander implements PlaceholderExpander {

	public const INITIALLY_COLLAPSED_SETTING_NAME = 'wikibase-entitytermsview-showEntitytermslistview';

	private TemplateFactory $templateFactory;
	private UserIdentity $user;
	private EntityDocument $entity;
	private LanguageDirectionalityLookup $languageDirectionalityLookup;
	private LanguageNameLookup $languageNameLookup;
	private LocalizedTextProvider $textProvider;
	private UserOptionsLookup $userOptionsLookup;
	private LanguageFallbackChainFactory $languageFallbackChainFactory;
	private bool $mulEnabled;

	/**
	 * @var string[]
	 */
	private array $termsLanguages;
	/**
	 * @var string[]
	 */
	private array $termsListItems;

	/**
	 * @param TemplateFactory $templateFactory
	 * @param UserIdentity $user the current user
	 * @param EntityDocument $entity
	 * @param string[] $termsLanguages
	 * @param LanguageDirectionalityLookup $languageDirectionalityLookup
	 * @param LanguageNameLookup $languageNameLookup
	 * @param LocalizedTextProvider $textProvider
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param LanguageFallbackChainFactory $languageFallbackChainFactory
	 * @param bool $mulEnabled
	 * @param string[] $termsListItems
	 */
	public function __construct(
		TemplateFactory $templateFactory,
		UserIdentity $user,
		EntityDocument $entity,
		array $termsLanguages,
		LanguageDirectionalityLookup $languageDirectionalityLookup,
		LanguageNameLookup $languageNameLookup,
		LocalizedTextProvider $textProvider,
		UserOptionsLookup $userOptionsLookup,
		LanguageFallbackChainFactory $languageFallbackChainFactory,
		bool $mulEnabled,
		array $termsListItems = []
	) {
		$this->user = $user;
		$this->entity = $entity;
		$this->templateFactory = $templateFactory;
		$this->termsLanguages = $termsLanguages;
		$this->languageDirectionalityLookup = $languageDirectionalityLookup;
		$this->languageNameLookup = $languageNameLookup;
		$this->textProvider = $textProvider;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->termsListItems = $termsListItems;
		$this->languageFallbackChainFactory = $languageFallbackChainFactory;
		$this->mulEnabled = $mulEnabled;
	}

	/**
	 * Callback for expanding placeholders to HTML,
	 * for use as a callback passed to with TextInjector::inject().
	 *
	 * @note This delegates to expandPlaceholder, which encapsulates knowledge about
	 * the meaning of each placeholder name, as used by EntityView.
	 *
	 * @param string $name the name (or kind) of placeholder; determines how the expansion is done.
	 *
	 * @return string HTML to be substituted for the placeholder in the output.
	 */
	public function getHtmlForPlaceholder( $name ): string {
		return $this->expandPlaceholder( $name );
	}

	/**
	 * Dispatch the expansion of placeholders based on the name.
	 *
	 * @note This encodes knowledge about which placeholders are used by EntityView with what
	 *       intended meaning.
	 */
	private function expandPlaceholder( string $name ): string {
		switch ( $name ) {
			case 'termbox':
				return $this->renderTermBox();
			case 'entityViewPlaceholder-entitytermsview-entitytermsforlanguagelistview-class':
				return $this->isInitiallyCollapsed() ? 'wikibase-initially-collapsed' : '';
			default:
				wfWarn( "Unknown placeholder: $name" );
				return '(((' . htmlspecialchars( $name ) . ')))';
		}
	}

	/**
	 * @return bool If the terms list should be initially collapsed for the current user.
	 */
	private function isInitiallyCollapsed(): bool {
		if ( !$this->user->isRegistered() ) {
			return false;
		} else {
			return !$this->userOptionsLookup->getOption(
				$this->user,
				self::INITIALLY_COLLAPSED_SETTING_NAME,
				true
			);
		}
	}

	/**
	 * Generates HTML of the term box, to be injected into the entity page.
	 *
	 * @throws InvalidArgumentException
	 * @return string HTML
	 */
	private function renderTermBox(): string {
		$termsListView = new TermsListView(
			$this->templateFactory,
			$this->languageNameLookup,
			$this->textProvider,
			$this->languageDirectionalityLookup,
			$this->languageFallbackChainFactory,
			$this->mulEnabled
		);

		$contentHtml = '';
		foreach ( $this->termsLanguages as $languageCode ) {
			if ( isset( $this->termsListItems[ $languageCode ] ) ) {
				$contentHtml .= $this->termsListItems[ $languageCode ];
			} else {
				$contentHtml .= $termsListView->getListItemHtml(
					$this->entity instanceof LabelsProvider ? $this->entity->getLabels() : new TermList(),
					$this->entity instanceof DescriptionsProvider ? $this->entity->getDescriptions() : new TermList(),
					$this->entity instanceof AliasesProvider ? $this->entity->getAliasGroups() : null,
					$languageCode
				);
			}
		}

		return $termsListView->getListViewHtml( $contentHtml );
	}

}
