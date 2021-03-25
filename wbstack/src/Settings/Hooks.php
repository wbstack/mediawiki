<?php

// https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar
$wgHooks['SkinBuildSidebar'][] = function ( $skin, &$sidebar ) use ( $wikiInfo ) {
    $sidebar['Wikibase'][] = [
        'text'  => 'New Item',
        'href'  => '/wiki/Special:NewItem',
    ];
    $sidebar['Wikibase'][] = [
        'text'  => 'New Property',
        'href'  => '/wiki/Special:NewProperty',
    ];
    if( $wikiInfo->getSetting('wwExtEnableWikibaseLexeme') ) {
        $sidebar['Wikibase'][] = [
            'text'  => 'New Lexeme',
            'href'  => '/wiki/Special:NewLexeme',
        ];
    }
    $sidebar['Wikibase'][] = [
        'text'  => 'New Schema',
        'href'  => '/wiki/Special:NewEntitySchema',
    ];
    $sidebar['Wikibase'][] = [
        'text'  => 'Query Service',
        'href'  => '/query/',
    ];
    $sidebar['Wikibase'][] = [
        'text'  => 'Cradle',
        'href'  => '/tools/cradle/',
    ];
    $sidebar['Wikibase'][] = [
        'text'  => 'QuickStatements',
        'href'  => '/tools/quickstatements/',
    ];
};

//// CUSTOM HOOKS
//TODO these should probably be in an extension...

class WBStackPageUpdateHandler {

    private static $titles = [];
    private static $hasScheduledDeferredUpdate = false;

    public static function registerUpdate( $title ) {
        self::$titles[$title->getPrefixedDBkey()] = [ $title->getDBkey(), $title->getNamespace() ];
        self::scheduleDeferredUpdateIfNeeded();
    }

    private static function scheduleDeferredUpdateIfNeeded() {
        global $wikiInfo;
        if( !self::$hasScheduledDeferredUpdate ) {
            self::$hasScheduledDeferredUpdate = true;
            $data = [];
            foreach( self::$titles as $titleData ) {
                $data[] = [
                    'wiki_id' => $wikiInfo->id,
                    'title' => $titleData[0],
                    'namespace' => $titleData[1],
                ];
            }
            \DeferredUpdates::addCallableUpdate( function() use ( $data ) {
                $options = [];
                $options['userAgent'] = 'WBStackPageUpdateHandler MediaWiki Event Submitter';
                $options['method'] = 'POST';
                $options['timeout'] = 5;
                $options['postData'] = json_encode($data);
                $request = \MWHttpRequest::factory(
                    'http://' . getenv( 'PLATFORM_API_BACKEND_HOST' ) . '/backend/event/pageUpdateBatch',
                    $options
                );
                $status = $request->execute();
                if ( !$status->isOK() ) {
                    wfDebugLog('WBSTACK', 'Failed to call platform event/pageUpdateBatch endpoint in WBStackPageUpdateHandler: ' . $status->getStatusValue());
                }
            });
        }
    }
}

// https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete
$wgHooks['PageContentSaveComplete'][] = function ( $wikiPage, $user, $mainContent, $summaryText, $isMinor, $isWatch, $section, &$flags, $revision, $status, $originalRevId, $undidRevId ) {
    WBStackPageUpdateHandler::registerUpdate( $wikiPage->getTitle() );
};

// https://www.mediawiki.org/wiki/Manual:Hooks/ArticleDeleteComplete
$wgHooks['ArticleDeleteComplete'][] = function ( $wikiPage, &$user, $reason, $id, $content, $logEntry, $archivedRevisionCount ) {
    WBStackPageUpdateHandler::registerUpdate( $wikiPage->getTitle() );
};

// https://www.mediawiki.org/wiki/Manual:Hooks/TitleMoveComplete
$wgHooks['TitleMoveComplete'][] = function ( $title, $newTitle, $user, $oldid, $newid, $reason, $revision ) {
    WBStackPageUpdateHandler::registerUpdate( $title );
    WBStackPageUpdateHandler::registerUpdate( $newTitle );
};

// https://www.mediawiki.org/wiki/Manual:Hooks/ArticleDeleteComplete
$wgHooks['ArticleUndelete'][] = function ( $title, $create, $comment, $oldPageId, $restoredPages ) {
    WBStackPageUpdateHandler::registerUpdate( $title );
};
