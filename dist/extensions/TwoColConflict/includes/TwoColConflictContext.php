<?php

namespace TwoColConflict;

use MediaWiki\Config\Config;
use MediaWiki\Extension\BetaFeatures\BetaFeatures;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\UserIdentity;
use MobileContext;
use WikiPage;

/**
 * @license GPL-2.0-or-later
 */
class TwoColConflictContext {

	public const BETA_PREFERENCE_NAME = 'twocolconflict';
	public const ENABLED_PREFERENCE = 'twocolconflict-enabled';
	public const HIDE_CORE_HINT_PREFERENCE = 'userjs-twocolconflict-hide-core-hint';

	private Config $config;
	private UserOptionsLookup $userOptionsLookup;
	private ExtensionRegistry $extensionRegistry;
	private ?MobileContext $mobileContext;

	public function __construct(
		Config $config,
		UserOptionsLookup $userOptionsLookup,
		ExtensionRegistry $extensionRegistry,
		?MobileContext $mobileContext = null
	) {
		$this->config = $config;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->extensionRegistry = $extensionRegistry;
		$this->mobileContext = $mobileContext;
	}

	/**
	 * @param UserIdentity $user
	 *
	 * @return bool True if the feature is not used as a beta feature, the
	 * user has disabled the feature but has not dismissed the core hint
	 * already.
	 */
	public function shouldCoreHintBeShown( UserIdentity $user ): bool {
		return $user->isRegistered() &&
			!$this->isUsedAsBetaFeature() &&
			!$this->userOptionsLookup->getBoolOption( $user, self::ENABLED_PREFERENCE ) &&
			!$this->userOptionsLookup->getBoolOption( $user, self::HIDE_CORE_HINT_PREFERENCE );
	}

	/**
	 * @param UserIdentity $user
	 * @param Title $title
	 *
	 * @return bool True if the new conflict interface should be used for this
	 *   user and title.  The user may have opted out, or the titles namespace
	 *   may be excluded for this interface.
	 */
	public function shouldTwoColConflictBeShown( UserIdentity $user, Title $title ): bool {
		if ( !$title->hasContentModel( CONTENT_MODEL_WIKITEXT ) &&
			!$title->hasContentModel( CONTENT_MODEL_TEXT )
		) {
			return false;
		}

		// T249817: Temporarily disabled on mobile
		if ( $this->mobileContext && $this->mobileContext->shouldDisplayMobileView() ) {
			return false;
		}

		if ( $this->isEligibleTalkPage( $title ) &&
			!$this->isTalkPageSuggesterEnabled()
		) {
			// Temporary feature logic to completely disable on talk pages.
			return false;
		}

		return $this->hasUserEnabledFeature( $user );
	}

	/**
	 * @param WikiPage $page
	 * @param UserIdentity $user
	 * @return bool True if this article is appropriate for the talk page
	 *   workflow, and the interface has been enabled by configuration.
	 */
	public function shouldTalkPageSuggestionBeConsidered( WikiPage $page, UserIdentity $user ): bool {
		return $this->isTalkPageSuggesterEnabled() &&
			$this->isEligibleTalkPage( $page->getTitle() ) &&
			!$this->isSelfConflict( $page, $user );
	}

	private function isSelfConflict( WikiPage $page, UserIdentity $user ): bool {
		$lastRevision = $page->getRevisionRecord();
		return $lastRevision && $user->equals( $lastRevision->getUser() );
	}

	private function hasUserEnabledFeature( UserIdentity $user ): bool {
		if ( $this->isUsedAsBetaFeature() ) {
			return BetaFeatures::isFeatureEnabled( $user, self::BETA_PREFERENCE_NAME );
		}

		return $this->userOptionsLookup->getBoolOption( $user, self::ENABLED_PREFERENCE );
	}

	/**
	 * @return bool True if TwoColConflict should be provided as a beta feature.
	 *   False if it will be the default conflict workflow.
	 */
	public function isUsedAsBetaFeature(): bool {
		return $this->config->get( 'TwoColConflictBetaFeature' ) &&
			$this->extensionRegistry->isLoaded( 'BetaFeatures' );
	}

	private function isEligibleTalkPage( Title $title ): bool {
		return $title->isTalkPage() || $title->inNamespace( NS_PROJECT );
	}

	private function isTalkPageSuggesterEnabled(): bool {
		return $this->config->get( 'TwoColConflictSuggestResolution' );
	}

}
