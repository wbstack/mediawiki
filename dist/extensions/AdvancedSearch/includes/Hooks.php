<?php

namespace AdvancedSearch;

use MediaWiki\Config\Config;
use MediaWiki\Hook\SpecialSearchResultsPrependHook;
use MediaWiki\Html\Html;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Output\OutputPage;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Request\WebRequest;
use MediaWiki\SpecialPage\Hook\SpecialPageBeforeExecuteHook;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Specials\SpecialSearch;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MessageLocalizer;
use SearchEngineConfig;
use Wikimedia\Mime\MimeAnalyzer;

/**
 * @license GPL-2.0-or-later
 */
class Hooks implements
	SpecialPageBeforeExecuteHook,
	GetPreferencesHook,
	SpecialSearchResultsPrependHook
{

	private UserOptionsLookup $userOptionsLookup;
	private LanguageNameUtils $languageNameUtils;
	private SearchEngineConfig $searchEngineConfig;
	private MimeAnalyzer $mimeAnalyzer;

	public function __construct(
		UserOptionsLookup $userOptionsLookup,
		LanguageNameUtils $languageNameUtils,
		SearchEngineConfig $searchEngineConfig,
		MimeAnalyzer $mimeAnalyzer
	) {
		$this->userOptionsLookup = $userOptionsLookup;
		$this->languageNameUtils = $languageNameUtils;
		$this->searchEngineConfig = $searchEngineConfig;
		$this->mimeAnalyzer = $mimeAnalyzer;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialPageBeforeExecute
	 *
	 * @param SpecialPage $special
	 * @param string|null $subpage
	 * @return false|void false to abort the execution of the special page, "void" otherwise
	 */
	public function onSpecialPageBeforeExecute( $special, $subpage ) {
		if ( $special->getName() !== 'Search' ) {
			return;
		}

		$user = $special->getUser();
		$outputPage = $special->getOutput();

		/**
		 * If the user is logged in and has explicitly requested to disable the extension, don't load.
		 * Ensure namespaces are always part of search URLs
		 */
		if ( $user->isNamed() &&
			$this->userOptionsLookup->getBoolOption( $user, 'advancedsearch-disable' )
		) {
			return;
		}

		$outputPage->addModules( [
			'ext.advancedSearch.init',
			'ext.advancedSearch.searchtoken',
		] );

		$outputPage->addModuleStyles( 'ext.advancedSearch.initialstyles' );

		$outputPage->addJsConfigVars( $this->getJsConfigVars(
			$special->getRequest(),
			$special->getUser(),
			$special->getContext(),
			$special->getConfig(),
			ExtensionRegistry::getInstance()
		) );
	}

	/**
	 * @param WebRequest $request
	 * @param User $user
	 * @param MessageLocalizer $context
	 * @param Config $config
	 * @param ExtensionRegistry $extensionRegistry
	 * @return array
	 */
	private function getJsConfigVars(
		WebRequest $request,
		User $user,
		MessageLocalizer $context,
		Config $config,
		ExtensionRegistry $extensionRegistry
	): array {
		$vars = [
			'advancedSearch.mimeTypes' =>
				( new MimeTypeConfigurator( $this->mimeAnalyzer ) )->getMimeTypes(
					$config->get( 'FileExtensions' )
				),
			'advancedSearch.tooltips' => ( new TooltipGenerator( $context ) )->generateTooltips(),
			'advancedSearch.namespacePresets' => $config->get( 'AdvancedSearchNamespacePresets' ),
			'advancedSearch.deepcategoryEnabled' => $config->get( 'AdvancedSearchDeepcatEnabled' ),
			'advancedSearch.searchableNamespaces' =>
				SearchableNamespaceListBuilder::getCuratedNamespaces(
					$this->searchEngineConfig->searchableNamespaces()
				),
		];

		if ( !self::isNamespacedSearch( $request ) ) {
			$vars += [ 'advancedSearch.defaultNamespaces' => $this->getDefaultNamespaces( $user ) ];
		}

		if ( $extensionRegistry->isLoaded( 'Translate' ) ) {
			$vars += [ 'advancedSearch.languages' =>
				$this->languageNameUtils->getLanguageNames()
			];
		}

		return $vars;
	}

	/**
	 * Retrieves the default namespaces for the current user
	 *
	 * @param UserIdentity $user The user to lookup default namespaces for
	 * @return int[] List of namespaces to be searched by default
	 */
	private function getDefaultNamespaces( UserIdentity $user ): array {
		return $this->searchEngineConfig->userNamespaces( $user ) ?: $this->searchEngineConfig->defaultNamespaces();
	}

	/**
	 * Checks if there is a search request, and it already specifies namespaces.
	 * @param WebRequest $request
	 * @return bool
	 */
	private static function isNamespacedSearch( WebRequest $request ): bool {
		if ( ( $request->getRawVal( 'search' ) ?? '' ) === '' ) {
			return true;
		}

		foreach ( $request->getValueNames() as $requestKey ) {
			if ( preg_match( '/^ns\d+$/', $requestKey ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SpecialSearchResultsPrepend
	 *
	 * @param SpecialSearch $specialSearch
	 * @param OutputPage $output
	 * @param string $term
	 */
	public function onSpecialSearchResultsPrepend( $specialSearch, $output, $term ) {
		$output->addHTML(
			Html::rawElement(
				'div',
				[ 'class' => 'mw-search-spinner' ],
				Html::element( 'div', [ 'class' => 'mw-search-spinner-bounce' ] )
			)
		);
	}

	/**
	 * @param User $user
	 * @param array[] &$preferences
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['advancedsearch-disable'] = [
			'type' => 'toggle',
			'label-message' => 'advancedsearch-preference-disable',
			'section' => 'searchoptions/advancedsearch',
			'help-message' => 'advancedsearch-preference-help',
		];
	}
}
