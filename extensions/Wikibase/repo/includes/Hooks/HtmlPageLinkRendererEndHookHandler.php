<?php

namespace Wikibase\Repo\Hooks;

use Action;
use HtmlArmor;
use MediaWiki\Interwiki\InterwikiLookup;
use MediaWiki\Linker\Hook\HtmlPageLinkRendererEndHook;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Special\SpecialPageFactory;
use RequestContext;
use Title;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\LabelDescriptionLookup;
use Wikibase\DataModel\Services\Lookup\LabelDescriptionLookupException;
use Wikibase\DataModel\Services\Lookup\TermLookup;
use Wikibase\DataModel\Term\TermFallback;
use Wikibase\Lib\LanguageFallbackChainFactory;
use Wikibase\Lib\SettingsArray;
use Wikibase\Lib\Store\EntityExistenceChecker;
use Wikibase\Lib\Store\EntityNamespaceLookup;
use Wikibase\Lib\Store\EntityUrlLookup;
use Wikibase\Lib\Store\LanguageFallbackLabelDescriptionLookup;
use Wikibase\Lib\Store\LinkTargetEntityIdLookup;
use Wikibase\Repo\FederatedProperties\FederatedPropertiesException;
use Wikibase\Repo\Hooks\Formatters\EntityLinkFormatterFactory;
use Wikibase\Repo\WikibaseRepo;

/**
 * Handler for the HtmlPageLinkRendererEnd hook, used to change the default link text of links to
 * wikibase Entity pages to the respective entity's label. This is used mainly for listings on
 * special pages or for edit summaries, where it is useful to see pages listed by label rather than
 * their entity ID.
 *
 * Label lookups are relatively expensive if done repeatedly for individual labels. If possible,
 * labels should be pre-loaded and buffered for later use via the HtmlPageLinkRendererEnd hook.
 *
 * @see LabelPrefetchHookHandler
 *
 * @license GPL-2.0-or-later
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class HtmlPageLinkRendererEndHookHandler implements HtmlPageLinkRendererEndHook {

	/**
	 * @var EntityExistenceChecker
	 */
	private $entityExistenceChecker;

	/**
	 * @var EntityIdParser
	 */
	private $entityIdParser;

	/**
	 * @var TermLookup
	 */
	private $termLookup;

	/**
	 * @var EntityNamespaceLookup
	 */
	private $entityNamespaceLookup;

	/**
	 * @var InterwikiLookup
	 */
	private $interwikiLookup;

	/**
	 * @var callable
	 */
	private $linkFormatterFactoryCallback;

	/**
	 * @var SpecialPageFactory
	 */
	private $specialPageFactory;

	/**
	 * @var LanguageFallbackChainFactory
	 */
	private $languageFallbackChainFactory;

	/**
	 * @var LabelDescriptionLookup|null
	 */
	private $labelDescriptionLookup;

	/**
	 * @var EntityUrlLookup
	 */
	private $entityUrlLookup;

	/**
	 * @var LinkTargetEntityIdLookup
	 */
	private $linkTargetEntityIdLookup;

	/**
	 * @var string|null
	 */
	private $federatedPropertiesSourceScriptUrl;

	/**
	 * @var bool
	 */
	private $federatedPropertiesEnabled;

	public static function factory(
		InterwikiLookup $interwikiLookup,
		SpecialPageFactory $specialPageFactory,
		EntityExistenceChecker $entityExistenceChecker,
		EntityIdParser $entityIdParser,
		EntityNamespaceLookup $entityNamespaceLookup,
		EntityUrlLookup $entityUrlLookup,
		LanguageFallbackChainFactory $languageFallbackChainFactory,
		LinkTargetEntityIdLookup $linkTargetEntityIdLookup,
		SettingsArray $repoSettings,
		TermLookup $termLookup
	): self {
		$wikibaseRepo = WikibaseRepo::getDefaultInstance();
		// NOTE: keep in sync with fallback chain construction in LabelPrefetchHookHandler::factory
		$context = RequestContext::getMain();

		return new self(
			$entityExistenceChecker,
			$entityIdParser,
			$termLookup,
			$entityNamespaceLookup,
			$interwikiLookup,
			function ( $language ) use ( $wikibaseRepo ) {
				return $wikibaseRepo->getEntityLinkFormatterFactory( $language );
			},
			$specialPageFactory,
			$languageFallbackChainFactory,
			$entityUrlLookup,
			$linkTargetEntityIdLookup,
			$repoSettings->getSetting( 'federatedPropertiesSourceScriptUrl' ),
			$repoSettings->getSetting( 'federatedPropertiesEnabled' )
		);
	}

	/**
	 * Special page handling where we want to display meaningful link labels instead of just the items ID.
	 * This is only handling special pages right now and gets disabled in normal pages.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/HtmlPageLinkRendererEnd
	 *
	 * @param LinkRenderer $linkRenderer
	 * @param LinkTarget $target
	 * @param bool $isKnown
	 * @param HtmlArmor|string|null &$text
	 * @param array &$extraAttribs
	 * @param string|null &$ret
	 *
	 * @return bool true to continue processing the link, false to use $ret directly as the HTML for the link
	 */
	public function onHtmlPageLinkRendererEnd(
		$linkRenderer,
		$target,
		$isKnown,
		&$text,
		&$extraAttribs,
		&$ret
	): bool {
		$context = RequestContext::getMain();
		if ( !$context->hasTitle() ) {
			// Short-circuit this hook if no title is
			// set in the main context (T131176)
			return true;
		}

		return $this->doHtmlPageLinkRendererEnd(
			$linkRenderer,
			Title::newFromLinkTarget( $target ),
			$text,
			$extraAttribs,
			$context,
			$ret
		);
	}

	public function __construct(
		EntityExistenceChecker $entityExistenceChecker,
		EntityIdParser $entityIdParser,
		TermLookup $termLookup,
		EntityNamespaceLookup $entityNamespaceLookup,
		InterwikiLookup $interwikiLookup,
		callable $linkFormatterFactoryCallback,
		SpecialPageFactory $specialPageFactory,
		LanguageFallbackChainFactory $languageFallbackChainFactory,
		EntityUrlLookup $entityUrlLookup,
		LinkTargetEntityIdLookup $linkTargetEntityIdLookup,
		?string $federatedPropertiesSourceScriptUrl,
		bool $federatedPropertiesEnabled
	) {
		$this->entityExistenceChecker = $entityExistenceChecker;
		$this->entityIdParser = $entityIdParser;
		$this->termLookup = $termLookup;
		$this->entityNamespaceLookup = $entityNamespaceLookup;
		$this->interwikiLookup = $interwikiLookup;
		$this->linkFormatterFactoryCallback = $linkFormatterFactoryCallback;
		$this->specialPageFactory = $specialPageFactory;
		$this->languageFallbackChainFactory = $languageFallbackChainFactory;
		$this->entityUrlLookup = $entityUrlLookup;
		$this->linkTargetEntityIdLookup = $linkTargetEntityIdLookup;
		$this->federatedPropertiesSourceScriptUrl = $federatedPropertiesSourceScriptUrl;
		$this->federatedPropertiesEnabled = $federatedPropertiesEnabled;
	}

	/**
	 * @param LinkRenderer $linkRenderer
	 * @param Title $target
	 * @param HtmlArmor|string|null &$text
	 * @param array &$customAttribs
	 * @param RequestContext $context
	 * @param string|null &$html
	 *
	 * @return bool true to continue processing the link, false to use $html directly for the link
	 */
	public function doHtmlPageLinkRendererEnd(
		LinkRenderer $linkRenderer,
		Title $target,
		&$text,
		array &$customAttribs,
		RequestContext $context,
		&$html = null
	) {
		$outTitle = $context->getOutput()->getTitle();
		$linkFormatterFactory = call_user_func( $this->linkFormatterFactoryCallback, $context->getLanguage() );

		// For good measure: Don't do anything in case the OutputPage has no Title set.
		if ( !$outTitle ) {
			return true;
		}

		// if custom link text is given, there is no point in overwriting it
		// but not if it is similar to the plain title
		if ( $text !== null && $target->getFullText() !== HtmlArmor::getHtml( $text ) ) {
			return true;
		}

		// Only continue on pages with edit summaries (histories / diffs) or on special pages.
		// Don't run this code when accessing it through the api (eg. for parsing) as the title is
		// set to a special page dummy in api.php, see https://phabricator.wikimedia.org/T111346
		if ( $this->isApiRequest() || !$this->shouldConvert( $outTitle, $context ) ) {
			return true;
		}

		try {
			return $this->internalDoHtmlPageLinkRendererEnd(
				$linkRenderer, $target, $text, $customAttribs, $context, $linkFormatterFactory, $html );
		} catch ( FederatedPropertiesException $ex ) {
			$this->federatedPropsDegradedDoHtmlPageLinkRendererEnd( $target, $text, $customAttribs );

			return true;
		}
	}

	/**
	 * Hook handling logic for the HtmlPageLinkRendererEnd hook in case federated properties are
	 * enabled, but access to the source wiki failed.
	 *
	 * @param Title $linkTarget
	 * @param HtmlArmor|string|null &$text
	 * @param array &$customAttribs
	 */
	private function federatedPropsDegradedDoHtmlPageLinkRendererEnd(
		LinkTarget $linkTarget,
		&$text,
		array &$customAttribs
		): void {
		$entityId = $this->linkTargetEntityIdLookup->getEntityId( $linkTarget );
		$text = $entityId->getSerialization();

		// This is a hack and could probably use the TitleIsAlwaysKnown hook instead.
		// Filter out the "new" class to avoid red links for existing entities.
		$customAttribs['class'] = $this->removeNewClass( $customAttribs['class'] ?? '' );
		// Use the entity id as title, as we can't lookup the label
		$customAttribs['title'] = $entityId->getSerialization();

		$customAttribs['href'] = $this->federatedPropertiesSourceScriptUrl .
			'index.php?title=Special:EntityData/' . urlencode( $entityId->getSerialization() );
	}

	/**
	 * Parts of the hook handling logic for the HtmlPageLinkRendererEnd hook that potentially
	 * interact with entity storage.
	 *
	 * @param LinkRenderer $linkRenderer
	 * @param Title $target
	 * @param HtmlArmor|string|null &$text
	 * @param array &$customAttribs
	 * @param RequestContext $context
	 * @param EntityLinkFormatterFactory $linkFormatterFactory
	 * @param string|null &$html
	 *
	 * @return bool true to continue processing the link, false to use $html directly for the link
	 */
	private function internalDoHtmlPageLinkRendererEnd(
		LinkRenderer $linkRenderer,
		Title $target,
		&$text,
		array &$customAttribs,
		RequestContext $context,
		EntityLinkFormatterFactory $linkFormatterFactory,
		&$html = null
	) {
		$out = $context->getOutput();

		$foreignEntityId = $this->parseForeignEntityId( $target );
		if ( !$foreignEntityId && !$this->entityNamespaceLookup->isEntityNamespace( $target->getNamespace() )
		) {
			return true;
		}

		$targetText = $target->getText();

		$entityId = $this->linkTargetEntityIdLookup->getEntityId( $target );
		if ( !$entityId ) {
			// Handle "fake" titles for new entities as generated by
			// EditEntity::getContextForEditFilter(). For instance, a link to Property:NewProperty
			// would be replaced by a link to Special:NewProperty. This is useful in logs,
			// to indicate that the logged action occurred while creating an entity.
			if ( $this->specialPageFactory->exists( $targetText ) ) {
				$target = Title::makeTitle( NS_SPECIAL, $targetText );
				$html = $linkRenderer->makeKnownLink( $target );
				return false;
			}

			return true;
		}

		if ( $target->isRedirect() ) {
			$customAttribs['href'] = wfAppendQuery( $this->entityUrlLookup->getLinkUrl( $entityId ), [ 'redirect' => 'no' ] );
		} else {
			$customAttribs['href'] = $this->entityUrlLookup->getLinkUrl( $entityId );
		}

		if ( !$this->entityExistenceChecker->exists( $entityId ) ) {
			// The link points to a non-existing entity.
			return true;
		}

		// This is a hack and could probably use the TitleIsAlwaysKnown hook instead.
		// Filter out the "new" class to avoid red links for existing entities.
		$customAttribs['class'] = $this->removeNewClass( $customAttribs['class'] ?? '' );

		$labelDescriptionLookup = $this->getLabelDescriptionLookup( $context );
		try {
			$label = $labelDescriptionLookup->getLabel( $entityId );
			$description = $labelDescriptionLookup->getDescription( $entityId );
		} catch ( LabelDescriptionLookupException $ex ) {
			return true;
		}

		$labelData = $this->termFallbackToTermData( $label );
		$descriptionData = $this->termFallbackToTermData( $description );

		$linkFormatter = $linkFormatterFactory->getLinkFormatter( $entityId->getEntityType() );
		$text = new HtmlArmor( $linkFormatter->getHtml( $entityId, $labelData ) );

		$customAttribs['title'] = $linkFormatter->getTitleAttribute(
			$entityId,
			$labelData,
			$descriptionData
		);

		$fragment = $linkFormatter->getFragment( $entityId, $target->getFragment() );
		$target->setFragment( '#' . $fragment );

		// add wikibase styles in all cases, so we can format the link properly:
		$out->addModuleStyles( [ 'wikibase.common' ] );
		if ( $this->federatedPropertiesEnabled && $entityId instanceof PropertyId ) {
			$customAttribs[ 'class' ] = $customAttribs[ 'class' ] == '' ? 'fedprop' : $customAttribs[ 'class' ] . ' fedprop';
			$out->addModules( 'wikibase.federatedPropertiesLeavingSiteNotice' );
		}
		return true;
	}

	/**
	 * Remove the new class from a space separated list of classes.
	 *
	 * @param string $classes
	 * @return string
	 */
	private function removeNewClass( string $classes ): string {
		return implode( ' ', array_filter(
			preg_split( '/\s+/', $classes ),
			function ( $class ) {
				return $class !== 'new';
			}
		) );
	}

	/**
	 * @param TermFallback|null $term
	 * @return string[]|null
	 */
	private function termFallbackToTermData( TermFallback $term = null ) {
		if ( $term ) {
			return [
				'value' => $term->getText(),
				'language' => $term->getActualLanguageCode(),
			];
		}

		return null;
	}

	/**
	 * @param LinkTarget $target
	 *
	 * @return EntityId|null
	 */
	private function parseForeignEntityId( LinkTarget $target ) {
		$interwiki = $target->getInterwiki();

		if ( $interwiki === '' || !$this->interwikiLookup->isValidInterwiki( $interwiki ) ) {
			return null;
		}

		$idPart = $this->extractForeignIdString( $target );

		$idPrefix = '';

		if ( $idPart !== null ) {
			try {
				// FIXME: This assumes repository name is equal to interwiki. This assumption might
				// become invalid
				return $this->entityIdParser->parse( EntityId::joinSerialization( [ $idPrefix, '', $idPart ] ) );
			} catch ( EntityIdParsingException $ex ) {
			}
		}

		return null;
	}

	/**
	 * Should be given an already confirmed valid interwiki link that uses Special:EntityPage
	 * to link to an entity on a remote Wikibase
	 */
	private function extractForeignIdString( LinkTarget $linkTarget ): ?string {
		return $this->extractForeignIdStringMainNs( $linkTarget ) ?: $this->extractForeignIdStringSpecialNs( $linkTarget );
	}

	private function extractForeignIdStringMainNs( LinkTarget $linkTarget ): ?string {
		if ( $linkTarget->getNamespace() !== NS_MAIN ) {
			return null;
		}

		$linkTargetChangedNamespace = Title::newFromText( $linkTarget->getText() );

		if ( $linkTargetChangedNamespace === null ) {
			return null;
		}

		return $this->extractForeignIdStringSpecialNs( $linkTargetChangedNamespace );
	}

	private function extractForeignIdStringSpecialNs( LinkTarget $linkTarget ): ?string {
		// FIXME: This encodes knowledge from EntityContentFactory::getTitleForId
		$prefix = 'EntityPage/';
		$prefixLength = strlen( $prefix );
		$pageName = $linkTarget->getText();

		if ( $linkTarget->getNamespace() === NS_SPECIAL && strncmp( $pageName, $prefix, $prefixLength ) === 0 ) {
			return substr( $pageName, $prefixLength );
		}

		return null;
	}

	/**
	 * Whether this is an API request.
	 *
	 * @return bool
	 */
	private function isApiRequest(): bool {
		return defined( 'MW_API' )
			&& MW_API !== 'TEST'; // T269608
	}

	/**
	 * Whether we should try to convert links on this page.
	 * This caches that result within a static variable,
	 * thus it can't change (except in phpunit tests).
	 *
	 * @param Title|null $currentTitle
	 * @param RequestContext $context
	 *
	 * @return bool
	 */
	private function shouldConvert( ?Title $currentTitle, RequestContext $context ) {
		static $shouldConvert = null;
		if ( $shouldConvert !== null && !defined( 'MW_PHPUNIT_TEST' ) ) {
			return $shouldConvert;
		}

		$actionName = Action::getActionName( $context );
		 // This is how Article detects diffs
		$isDiff = $actionName === 'view' && $context->getRequest()->getCheck( 'diff' );

		// Only continue on pages with edit summaries (histories / diffs) or on special pages.
		if (
			( $currentTitle === null || !$currentTitle->isSpecialPage() )
			&& $actionName !== 'history'
			&& !$isDiff
		) {
			// Note: this may not work right with special page transclusion. If $out->getTitle()
			// doesn't return the transcluded special page's title, the transcluded text will
			// not have entity IDs resolved to labels.
			$shouldConvert = false;
			return false;
		}

		$shouldConvert = true;
		return true;
	}

	private function getLabelDescriptionLookup( RequestContext $context ): LabelDescriptionLookup {
		if ( $this->labelDescriptionLookup === null ) {
			$this->labelDescriptionLookup = new LanguageFallbackLabelDescriptionLookup(
				$this->termLookup,
				$this->languageFallbackChainFactory->newFromContext( $context )
			);
		}

		return $this->labelDescriptionLookup;
	}

}
