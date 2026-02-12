<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace TwoColConflict\Hooks;

use MediaWiki\EditPage\EditPage;
use MediaWiki\Extension\EventLogging\EventLogging;
use MediaWiki\Hook\AlternateEditHook;
use MediaWiki\Hook\EditPage__showEditForm_fieldsHook;
use MediaWiki\Hook\EditPage__showEditForm_initialHook;
use MediaWiki\Hook\EditPageBeforeConflictDiffHook;
use MediaWiki\Hook\EditPageBeforeEditButtonsHook;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\User\Options\Hook\LoadUserOptionsHook;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use OOUI\ButtonInputWidget;
use TwoColConflict\ConflictFormValidator;
use TwoColConflict\Html\CoreUiHintHtml;
use TwoColConflict\SplitTwoColConflictHelper;
use TwoColConflict\TalkPageConflict\ResolutionSuggester;
use TwoColConflict\TwoColConflictContext;

/**
 * Hook handlers for the TwoColConflict extension.
 *
 * @license GPL-2.0-or-later
 */
class TwoColConflictHooks implements
	GetPreferencesHook,
	LoadUserOptionsHook,
	AlternateEditHook,
	EditPageBeforeConflictDiffHook,
	EditPageBeforeEditButtonsHook,
	EditPage__showEditForm_initialHook,
	EditPage__showEditForm_fieldsHook
{

	private TwoColConflictContext $twoColContext;

	private static function newFromGlobalState(): self {
		return new self( MediaWikiServices::getInstance()->getService( 'TwoColConflictContext' ) );
	}

	public function __construct( TwoColConflictContext $twoColContext ) {
		$this->twoColContext = $twoColContext;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/AlternateEdit
	 *
	 * @param EditPage $editPage
	 */
	public function onAlternateEdit( $editPage ) {
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
				$services->getStatsFactory(),
				$submitButtonLabel,
				$services->getContentHandlerFactory(),
				$this->twoColContext,
				new ResolutionSuggester(
					$baseRevision,
					$wikiPage->getContentHandler()->getDefaultFormat()
				),
				$services->getCommentFormatter(),
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
	public function onEditPage__showEditForm_initial(
		$editPage,
		$outputPage
	) {
		// What the script does is only used for logging in doEditPageBeforeConflictDiff below
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
	public function onEditPage__showEditForm_fields(
		$editPage,
		$outputPage
	) {
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
	public function onEditPageBeforeConflictDiff(
		$editPage,
		$outputPage
	) {
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

			EventLogging::logEvent(
				'TwoColConflictConflict',
				-1,
				[
					'twoColConflictShown' => $this->twoColContext->shouldTwoColConflictBeShown(
						$user,
						$context->getTitle()
					),
					'isAnon' => !$user->isNamed(),
					'editCount' => (int)$user->getEditCount(),
					'pageNs' => $context->getTitle()->getNamespace(),
					'baseRevisionId' => $baseRevision ? $baseRevision->getId() : 0,
					'latestRevisionId' => $latestRevision ? $latestRevision->getId() : 0,
					// Previously we tried a 3-way-merge with the unsaved content and tracked some
					// not so sensitive metrics here, but this was expensive and fragile
					'conflictChunks' => -1,
					'conflictChars' => -1,
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
	public function onEditPageBeforeEditButtons(
		$editPage,
		&$buttons,
		&$tabindex
	) {
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
	private function doGetBetaFeaturePreferences( array &$prefs ): void {
		if ( $this->twoColContext->isUsedAsBetaFeature() ) {
			$config = MediaWikiServices::getInstance()->getMainConfig();
			$path = $config->get( MainConfigNames::ExtensionAssetsPath );
			$prefs[TwoColConflictContext::BETA_PREFERENCE_NAME] = [
				'label-message' => 'twocolconflict-beta-feature-message',
				'desc-message' => 'twocolconflict-beta-feature-description',
				'screenshot' => [
					'ltr' => "$path/TwoColConflict/resources/TwoColConflict-beta-features-ltr.svg",
					'rtl' => "$path/TwoColConflict/resources/TwoColConflict-beta-features-rtl.svg",
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
	public function onGetPreferences( $user, &$preferences ) {
		if ( $this->twoColContext->isUsedAsBetaFeature() ) {
			return;
		}

		$preferences[TwoColConflictContext::ENABLED_PREFERENCE] = [
			'type' => 'toggle',
			'label-message' => 'twocolconflict-preference-enabled',
			'section' => 'editing/advancedediting',
		];
	}

	public function onLoadUserOptions( UserIdentity $user, array &$options ): void {
		if ( $this->twoColContext->isUsedAsBetaFeature() ) {
			return;
		}

		// Drop obsolete option from the database. The original plan was to migrate the Beta opt-in
		// to the later opt-out. This is not possible. Every user who changed some option will also
		// have this option set. Impossible to know if the Beta feature was intentionally disabled.
		unset( $options[TwoColConflictContext::BETA_PREFERENCE_NAME] );
	}

}
