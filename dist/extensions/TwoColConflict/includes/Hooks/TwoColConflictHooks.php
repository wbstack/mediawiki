<?php

namespace TwoColConflict\Hooks;

use EditPage;
use ExtensionRegistry;
use MediaWiki\Extension\EventLogging\EventLogging;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\UserIdentity;
use OOUI\ButtonInputWidget;
use OutputPage;
use TwoColConflict\ConflictFormValidator;
use TwoColConflict\Html\CoreUiHintHtml;
use TwoColConflict\Logging\ThreeWayMerge;
use TwoColConflict\SplitTwoColConflictHelper;
use TwoColConflict\TalkPageConflict\ResolutionSuggester;
use TwoColConflict\TwoColConflictContext;
use User;

/**
 * Hook handlers for the TwoColConflict extension.
 *
 * @license GPL-2.0-or-later
 */
class TwoColConflictHooks {

	/**
	 * @var TwoColConflictContext
	 */
	private $twoColContext;

	private static function newFromGlobalState() {
		return new self( MediaWikiServices::getInstance()->getService( 'TwoColConflictContext' ) );
	}

	private function __construct( TwoColConflictContext $twoColContext ) {
		$this->twoColContext = $twoColContext;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/AlternateEdit
	 *
	 * @param EditPage $editPage
	 */
	public static function onAlternateEdit( EditPage $editPage ) {
		self::newFromGlobalState()->doAlternateEdit( $editPage );
	}

	/**
	 * @param EditPage $editPage
	 */
	private function doAlternateEdit( EditPage $editPage ) {
		$context = $editPage->getContext();

		// Skip out if the feature is disabled
		if ( !$this->twoColContext->shouldTwoColConflictBeShown(
			$context->getUser(),
			$context->getTitle()
		) ) {
			return;
		}

		$editPage->setEditConflictHelperFactory( function ( $submitButtonLabel ) use ( $editPage ) {
			$services = MediaWikiServices::getInstance();
			$context = $editPage->getContext();
			$baseRevision = $editPage->getExpectedParentRevision();
			$title = $context->getTitle();
			$wikiPage = $services->getWikiPageFactory()->newFromTitle( $title );

			return new SplitTwoColConflictHelper(
				$title,
				$context->getOutput(),
				$services->getStatsdDataFactory(),
				$submitButtonLabel,
				$services->getContentHandlerFactory(),
				$this->twoColContext,
				new ResolutionSuggester(
					$baseRevision,
					$wikiPage->getContentHandler()->getDefaultFormat()
				),
				$services->getMainObjectStash(),
				$editPage->summary,
				$services->getUserOptionsLookup()->getOption( $context->getUser(), 'editfont' )
			);
		} );

		$request = $context->getRequest();
		if ( !( new ConflictFormValidator() )->validateRequest( $request ) ) {
			// Mark the conflict as *not* being resolved to trigger it again. This works because
			// EditPage uses editRevId to decide if it's even possible to run into a conflict.
			// If editRevId reflects the most recent revision, it can't be a conflict (again),
			// and the user's input is stored, even if it reverts everything.
			// Warning, this is particularly fragile! This assumes EditPage was not reading the
			// WebRequest values before!
			$request->setVal( 'editRevId', $request->getInt( 'parentRevId' ) );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPage::showEditForm:initial
	 * @codeCoverageIgnore this is only for logging, not a user-facing feature
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public static function onEditPageShowEditFormInitial(
		EditPage $editPage,
		OutputPage $outputPage
	) {
		if ( ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ) {
			$outputPage->addModules( 'ext.TwoColConflict.JSCheck' );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPage::showEditForm:fields
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public static function onEditPageShowEditFormFields(
		EditPage $editPage,
		OutputPage $outputPage
	) {
		self::newFromGlobalState()->doEditPageShowEditFormFields( $editPage, $outputPage );
	}

	/**
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public function doEditPageShowEditFormFields( EditPage $editPage, OutputPage $outputPage ) {
		// TODO remove this hint when we're sure people are aware of the new feature
		if ( $editPage->isConflict &&
			$this->twoColContext->shouldCoreHintBeShown( $outputPage->getUser() )
		) {
			$outputPage->enableOOUI();
			$outputPage->addModuleStyles( 'ext.TwoColConflict.SplitCss' );
			$outputPage->addModules( 'ext.TwoColConflict.SplitJs' );
			$outputPage->addHTML( ( new CoreUiHintHtml( $outputPage->getContext() ) )->getHtml() );
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageBeforeConflictDiff
	 * @codeCoverageIgnore this is only for logging, not a user-facing feature
	 *
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public static function onEditPageBeforeConflictDiff(
		EditPage $editPage,
		OutputPage $outputPage
	) {
		self::newFromGlobalState()->doEditPageBeforeConflictDiff( $editPage, $outputPage );
	}

	/**
	 * @param EditPage $editPage
	 * @param OutputPage $outputPage
	 */
	public function doEditPageBeforeConflictDiff( EditPage $editPage, OutputPage $outputPage ) {
		$context = $editPage->getContext();
		$title = $context->getTitle();
		$request = $context->getRequest();
		if ( $context->getConfig()->get( 'TwoColConflictTrackingOversample' ) ) {
			$request->setVal( 'editingStatsOversample', true );
		}

		if ( ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ) {
			$user = $outputPage->getUser();
			$baseRevision = $editPage->getExpectedParentRevision();
			$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
			$latestRevision = $revisionStore->getKnownCurrentRevision( $title );

			$conflictChunks = 0;
			$conflictChars = 0;
			if ( $baseRevision && $latestRevision ) {
				$baseContent = $baseRevision->getContent( SlotRecord::MAIN );
				$latestContent = $latestRevision->getContent( SlotRecord::MAIN );
				if ( $baseContent && $latestContent ) {
					// Attempt the automatic merge, to measure the number of actual conflicts.
					/** @var ThreeWayMerge $merge */
					$merge = MediaWikiServices::getInstance()->getService( 'TwoColConflictThreeWayMerge' );
					$result = $merge->merge3(
						$baseContent->serialize(),
						$latestContent->serialize(),
						$editPage->textbox2
					);

					if ( !$result->isCleanMerge() ) {
						$conflictChunks = $result->getOverlappingChunkCount();
						$conflictChars = $result->getOverlappingChunkSize();
					}
				}
			}

			EventLogging::logEvent(
				'TwoColConflictConflict',
				-1,
				[
					'twoColConflictShown' => $this->twoColContext->shouldTwoColConflictBeShown(
						$user,
						$context->getTitle()
					),
					'isAnon' => !$user->isRegistered(),
					'editCount' => (int)$user->getEditCount(),
					'pageNs' => $context->getTitle()->getNamespace(),
					'baseRevisionId' => $baseRevision ? $baseRevision->getId() : 0,
					'latestRevisionId' => $latestRevision ? $latestRevision->getId() : 0,
					'conflictChunks' => $conflictChunks,
					'conflictChars' => $conflictChars,
					'startTime' => $editPage->starttime ?: '',
					'editTime' => $editPage->edittime ?: '',
					'pageTitle' => $context->getTitle()->getText(),
					'hasJavascript' => $request->getBool( 'mw-twocolconflict-js' )
						|| $request->getBool( 'veswitched' ),
				]
			);
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPageBeforeEditButtons
	 *
	 * @param EditPage $editPage
	 * @param ButtonInputWidget[] &$buttons
	 * @param int &$tabindex
	 */
	public static function onEditPageBeforeEditButtons(
		EditPage $editPage,
		array &$buttons,
		&$tabindex
	) {
		self::newFromGlobalState()->doEditPageBeforeEditButtons( $editPage, $buttons );
	}

	/**
	 * @param EditPage $editPage
	 * @param ButtonInputWidget[] &$buttons
	 */
	public function doEditPageBeforeEditButtons( EditPage $editPage, array &$buttons ) {
		$context = $editPage->getContext();
		if ( $this->twoColContext->shouldTwoColConflictBeShown(
				$context->getUser(),
				$context->getTitle()
			) &&
			$editPage->isConflict === true
		) {
			unset( $buttons['diff'] );
			// T230152
			if ( isset( $buttons['preview'] ) ) {
				$buttons['preview']->setDisabled( true );
			}
		}
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetBetaFeaturePreferences
	 *
	 * @param User $user
	 * @param array[] &$prefs
	 */
	public static function onGetBetaFeaturePreferences( $user, array &$prefs ) {
		self::newFromGlobalState()->doGetBetaFeaturePreferences( $prefs );
	}

	/**
	 * @param array[] &$prefs
	 */
	public function doGetBetaFeaturePreferences( array &$prefs ) {
		if ( $this->twoColContext->isUsedAsBetaFeature() ) {
			$config = MediaWikiServices::getInstance()->getMainConfig();
			$extensionAssetsPath = $config->get( 'ExtensionAssetsPath' );
			$prefs[TwoColConflictContext::BETA_PREFERENCE_NAME] = [
				'label-message' => 'twocolconflict-beta-feature-message',
				'desc-message' => 'twocolconflict-beta-feature-description',
				'screenshot' => [
					'ltr' => "$extensionAssetsPath/TwoColConflict/resources/TwoColConflict-beta-features-ltr.svg",
					'rtl' => "$extensionAssetsPath/TwoColConflict/resources/TwoColConflict-beta-features-rtl.svg",
				],
				'info-link'
					=> 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Two_Column_Edit_Conflict_View',
				'discussion-link'
					=> 'https://www.mediawiki.org/wiki/Help_talk:Two_Column_Edit_Conflict_View',
			];
		}
	}

	/**
	 * @param User $user
	 * @param array[] &$preferences
	 */
	public static function onGetPreferences( $user, array &$preferences ) {
		self::newFromGlobalState()->doGetPreferences( $preferences );
	}

	/**
	 * @param array[] &$preferences
	 */
	public function doGetPreferences( array &$preferences ) {
		if ( $this->twoColContext->isUsedAsBetaFeature() ) {
			return;
		}

		$preferences[TwoColConflictContext::ENABLED_PREFERENCE] = [
			'type' => 'toggle',
			'label-message' => 'twocolconflict-preference-enabled',
			'section' => 'editing/advancedediting',
		];
	}

	/**
	 * @param UserIdentity $user
	 * @param array &$options
	 */
	public static function onLoadUserOptions( UserIdentity $user, array &$options ) {
		self::newFromGlobalState()->doLoadUserOptions( $options );
	}

	/**
	 * If a user is opted-out of the beta feature, that will be copied over to the newer
	 * preference.  This ensures that anyone who has opted-out continues to be so as we
	 * promote wikis out of beta feature mode.
	 *
	 * This entire function can be removed once all users have been migrated away from
	 * their beta feature preference.  See T250955.
	 *
	 * @param array &$options
	 */
	public function doLoadUserOptions( array &$options ) {
		if ( $this->twoColContext->isUsedAsBetaFeature() ) {
			return;
		}

		$betaPreference = $options[TwoColConflictContext::BETA_PREFERENCE_NAME] ?? null;
		if ( $betaPreference === 0 ) {
			$options[TwoColConflictContext::ENABLED_PREFERENCE] = 0;
		}
		$options[TwoColConflictContext::BETA_PREFERENCE_NAME] = null;
	}

}
