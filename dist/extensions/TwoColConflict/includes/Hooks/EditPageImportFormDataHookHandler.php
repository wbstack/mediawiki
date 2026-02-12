<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace TwoColConflict\Hooks;

use MediaWiki\EditPage\EditPage;
use MediaWiki\Hook\EditPage__importFormDataHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Request\WebRequest;
use TwoColConflict\ConflictFormValidator;
use TwoColConflict\SplitConflictMerger;
use TwoColConflict\TwoColConflictContext;

/**
 * @license GPL-2.0-or-later
 */
class EditPageImportFormDataHookHandler implements EditPage__importFormDataHook {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/EditPage::importFormData
	 *
	 * @param EditPage $editPage
	 * @param WebRequest $request
	 */
	public function onEditPage__importFormData( $editPage, $request ) {
		$contentRows = $request->getArray( 'mw-twocolconflict-split-content' );
		if ( $contentRows ) {
			$extraLineFeeds = $request->getArray( 'mw-twocolconflict-split-linefeeds' ) ?? [];
			$sideSelection = $request->getArray( 'mw-twocolconflict-side-selector' ) ?? [];

			if ( $request->getBool( 'mw-twocolconflict-single-column-view' ) ) {
				if ( !( new ConflictFormValidator() )->validateRequest( $request ) ) {
					// When the request is invalid, drop any selection to force the original conflict to
					// be re-created, and not silently resolved or corrupted.
					$sideSelection = [];
					foreach ( $contentRows as $num => &$row ) {
						// Make sure the merger can't fall back to "other", but allow other fallbacks
						if ( is_array( $row ) && key( $row ) === 'other' ) {
							unset( $row['other'] );
							if ( !$row ) {
								unset( $contentRows[$num] );
							}
						}
					}
				} elseif ( $request->getVal( 'mw-twocolconflict-reorder' ) === 'reverse' ) {
					[ $contentRows, $extraLineFeeds ] = self::swapTalkComments( $contentRows, $extraLineFeeds );
				}
			}

			$editPage->textbox1 = ( new SplitConflictMerger() )->mergeSplitConflictResults(
				$contentRows,
				$extraLineFeeds,
				$sideSelection
			);
		}

		if ( $request->getBool( 'mw-twocolconflict-disable-core-hint' ) ) {
			$user = $editPage->getContext()->getUser();
			if ( $user->isNamed() ) {
				$userOptionsManager = MediaWikiServices::getInstance()->getUserOptionsManager();
				$userOptionsManager->setOption( $user, TwoColConflictContext::HIDE_CORE_HINT_PREFERENCE, '1' );
				$userOptionsManager->saveOptions( $user );
			}
		}
	}

	private static function swapTalkComments( array $contentRows, array $extraLineFeeds ): array {
		for ( $i = 0; $i < count( $contentRows ) - 1; $i++ ) {
			if ( isset( $contentRows[$i]['other'] ) && isset( $contentRows[$i + 1]['your'] ) ) {
				[ $contentRows[$i], $contentRows[$i + 1] ] =
					[ $contentRows[$i + 1], $contentRows[$i] ];
				[ $extraLineFeeds[$i], $extraLineFeeds[$i + 1] ] =
					[ $extraLineFeeds[$i + 1] ?? 0, $extraLineFeeds[$i] ?? 0 ];
				$i++;
			}
		}

		return [ $contentRows, $extraLineFeeds ];
	}

}
