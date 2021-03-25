<?php

// If we have internal settings, and have been told to load them, then load them...
if( getenv('WBSTACK_LOAD_MW_INTERNAL') === 'yes' && file_exists( __DIR__ . '/src/loadInternal.php' ) ) {
    require_once __DIR__ . '/src/loadInternal.php';
} else {
    // Code for ONLY the public mw services
    $wgReservedUsernames = array_merge(
        $wgReservedUsernames,
        [
            // TODO should this be a constant
            'PlatformReservedUser',
        ]
    );
}

// Main stuff from the platform
$wgSitename = $wikiInfo->sitename;
$wgSecretKey = $wikiInfo->getSetting('wgSecretKey');
$wgLogos = [
    "1x" => $wikiInfo->getSetting('wgLogo'),
];
if( $wgLogos["1x"] === null ) {
    // Fallback to the mediawiki logo without the wgLogo overlay
    $wgLogos = [
        "1x" => "https://storage.googleapis.com/wbstack-static/assets/mediawiki/mediawiki.png",
    ];
}
$wgFavicon = $wikiInfo->getSetting('wgFavicon');
if( $wgFavicon === null ) {
    // Default from install, but maybe we want to change this?
    $wgFavicon = "/favicon.ico";
}
// TODO this should be settings from the main platform
$wgLanguageCode = "en";
## TODO allow turning some skins on and off?
$wgDefaultSkin = $wikiInfo->getSetting('wgDefaultSkin');
if( $wgDefaultSkin === null ) {
    // Fallback to vector
    $wgDefaultSkin = "vector";
}

// STORAGE - compress revisions
$wgCompressRevisions = true;

// Caches

// Don't specify a redis cache when running dbless maint script
// TODO we probably do want a redis connection in some maint scripts...
if(!$wwDomainSaysMaint) {
    /** @see RedisBagOStuff for a full explanation of these options. **/
    $wgMainCacheType = 'redis';
    $wgSessionCacheType = 'redis';
    $wgObjectCaches['redis'] = [
        'class' => 'ReplicatedBagOStuff',
        'readFactory' => [
            'factory' => [ 'ObjectCache', 'newFromParams' ],
            'args'  => [ [
                'class' => 'RedisBagOStuff',
                'servers' => [ getenv('MW_REDIS_SERVER_READ') ]
            ] ]
        ],
        'writeFactory' => [
            'factory' => [ 'ObjectCache', 'newFromParams' ],
            'args'  => [ [
                'class' => 'RedisBagOStuff',
                'servers' => [ getenv('MW_REDIS_SERVER_WRITE') ]
            ] ]
        ],
        'loggroup'  => 'RedisBagOStuff',
        'reportDupes' => false
    ];
    if(getenv('MW_REDIS_PASSWORD') !== '') {
        // Only set the password if not empty
        // TODO do this optional password setting in a less evil way...
        $wgObjectCaches['redis']['readFactory']['args'][0]['password'] = getenv('MW_REDIS_PASSWORD');
        $wgObjectCaches['redis']['writeFactory']['args'][0]['password'] = getenv('MW_REDIS_PASSWORD');
    }
}
// Modified default from https://www.mediawiki.org/wiki/Manual:$wgObjectCaches
// to have slightly more aggressive cache purging
$wgObjectCaches[CACHE_DB] = [
    'class' => SqlBagOStuff::class,
    'loggroup' => 'SQLBagOStuff',
    'args'  => [ [
        'purgePeriod' => 5,
        'purgeLimit' => 1000,
    ] ]
];
$wgObjectCaches['db-replicated'] = [
    'class'       => ReplicatedBagOStuff::class,
    'readFactory' => [
        'factory' => [ 'ObjectCache', 'newFromParams' ],
        'args'  => [ [
            'class' => SqlBagOStuff::class,
            'replicaOnly' => true,
            'purgePeriod' => 5,
            'purgeLimit' => 1000,
        ] ]
    ],
    'writeFactory' => [
        'factory' => [ 'ObjectCache', 'newFromParams' ],
        'args'  => [ [
            'class' => SqlBagOStuff::class,
            'replicaOnly' => false,
            'purgePeriod' => 5,
            'purgeLimit' => 1000,
        ] ]
    ],
    'loggroup'  => 'SQLBagOStuff',
    'reportDupes' => false
];

$wgParserCacheType = 'db-replicated'; // 'db-replicated' is defined in LocalSetting.pjp currently

// Know about proxies... so that we get the real IP..
$wgCdnServersNoPurge = [
    # IP range matches current kubernetes pod IPs for GKE
    '10.8.0.0/14'
];
// This one is needed prior to 1.34
$wgSquidServersNoPurge = $wgCdnServersNoPurge;

#######################################
## ---        Permissions        --- ##
#######################################

# Disallow anon editing for now
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createpage'] = false;

# Stop crats from being able to interact with the platform group
$wgGroupPermissions['bureaucrat']['userrights'] = false;
$wgAddGroups['bureaucrat'][] = 'sysop';
$wgAddGroups['bureaucrat'][] = 'bureaucrat';
$wgAddGroups['bureaucrat'][] = 'bot';
$wgAddGroups['bureaucrat'][] = 'emailconfirmed';
$wgRemoveGroups['bureaucrat'][] = 'sysop';
$wgRemoveGroups['bureaucrat'][] = 'bureaucrat';
$wgRemoveGroups['bureaucrat'][] = 'bot';
$wgRemoveGroups['bureaucrat'][] = 'emailconfirmed';

# Remove the predefined interface-admin group
unset( $wgGroupPermissions['interface-admin'] );
unset( $wgRevokePermissions['interface-admin'] );
unset( $wgAddGroups['interface-admin'] );
unset( $wgRemoveGroups['interface-admin'] );
unset( $wgGroupsAddToSelf['interface-admin'] );
unset( $wgGroupsRemoveFromSelf['interface-admin'] );

# Allow crats to editsitecss
$wgGroupPermissions['bureaucrat']['editsitecss'] = true;

# Disable user CSS and JS editing for now
$wgGroupPermissions['user']['editmyusercss'] = false;
$wgGroupPermissions['user']['editmyuserjs'] = false;

# Allow emailconfirmed to skip captcha
$wgGroupPermissions['emailconfirmed']['skipcaptcha'] = true;

# Oauth
$wgGroupPermissions['sysop']['mwoauthproposeconsumer'] = true;
$wgGroupPermissions['sysop']['mwoauthmanageconsumer'] = true;
$wgGroupPermissions['sysop']['mwoauthviewprivate'] = true;
$wgGroupPermissions['sysop']['mwoauthupdateownconsumer'] = true;
$wgGroupPermissions['platform']['mwoauthproposeconsumer'] = true;
$wgGroupPermissions['platform']['mwoauthmanageconsumer'] = true;
$wgGroupPermissions['platform']['mwoauthviewprivate'] = true;
$wgGroupPermissions['platform']['mwoauthupdateownconsumer'] = true;

#######################################
## ---         Extensions        --- ##
#######################################

# MobileFrontend
$wgMFDefaultSkinClass = 'SkinMinerva';

# MailGun
$wgMailgunAPIKey = getenv('MW_MAILGUN_API_KEY');
$wgMailgunDomain = getenv('MW_MAILGUN_DOMAIN');

# ConfirmEdit
$wgCaptchaClass = 'ReCaptchaNoCaptcha';
$wgReCaptchaSendRemoteIP = true;
$wgReCaptchaSiteKey = getenv('MW_RECAPTCHA_SITEKEY');
$wgReCaptchaSecretKey = getenv('MW_RECAPTCHA_SECRETKEY');

# WikibaseInWikitext (custom wbstack extension)
$wgWikibaseInWikitextSparqlDefaultUi = $wwWbSiteBaseUri . '/query';

# TwoColConflict
$wgTwoColConflictBetaFeature = false;

# Score
$wgMusicalNotationEnableWikibaseDataType = true;


// Wikibase
$wwWbSiteBaseUri = preg_replace( '!^//!', 'http://', $GLOBALS['wgServer'] );
$wwWbConceptUri = $wwWbSiteBaseUri . '/entity/';

$wwWikibaseStringLengthString = $wikiInfo->getSetting('wwWikibaseStringLengthMonolingualText');
if($wwWikibaseStringLengthString) {
    $wgWBRepoSettings['string-limits']['VT:string']['length'] = (int)$wwWikibaseStringLengthString;
}
$wwWikibaseStringLengthMonolingualText = $wikiInfo->getSetting('wwWikibaseStringLengthMonolingualText');
if($wwWikibaseStringLengthMonolingualText) {
    $wgWBRepoSettings['string-limits']['VT:monolingualtext']['length'] = (int)$wwWikibaseStringLengthMonolingualText;
}
$wwWikibaseStringLengthMultilang = $wikiInfo->getSetting('wwWikibaseStringLengthMultilang');
if($wwWikibaseStringLengthMultilang) {
    $wgWBRepoSettings['string-limits']['multilang']['length'] = (int)$wwWikibaseStringLengthMultilang;
}

$wgWBClientSettings['siteGlobalID'] = $wgDBname;
$wgWBClientSettings['repoScriptPath'] = '/w';
$wgWBClientSettings['repoArticlePath'] = '/wiki/$1';
$wgWBClientSettings['siteGroup'] = null;
$wgWBClientSettings['thisWikiIsTheRepo'] = true;
$wgWBClientSettings['repoUrl'] = $GLOBALS['wgServer'];
$wgWBClientSettings['repoSiteName'] = $GLOBALS['wgSitename'];
$wgWBClientSettings['repositories'] = [
    '' => [
        // Use false (meaning the local wiki's database) if this wiki is the repo,
        // otherwise default to null (meaning we can't access the repo's DB directly).
        'repoDatabase' => false,
        'baseUri' => $wwWbConceptUri,
        'entityNamespaces' => [
            'item' => 120,
            'property' => 122,
        ],
        'prefixMapping' => [ '' => '' ],
    ]
];

// TODO below setting will be empty by default in the future and we could remove them
$wgWBRepoSettings['siteLinkGroups'] = [];
// TODO below setting will be empty by default in the future and we could remove them
$wgWBRepoSettings['specialSiteLinkGroups'] = [];
$wgWBRepoSettings['dataRightsUrl'] = null;
$wgWBRepoSettings['dataRightsText'] = 'None yet set.';
$wgWBRepoSettings['conceptBaseUri'] = $wwWbConceptUri;

// Until we can scale redis memory we don't want to do this - https://github.com/addshore/wbstack/issues/37
$wgWBRepoSettings['sharedCacheType'] = CACHE_NONE;

// InviteSignup
if( $wikiInfo->getSetting('wwExtEnableInviteSignup') ) {
    # Restrict account creation
    $wgGroupPermissions['*']['createaccount'] = false;
    $wgGroupPermissions['user']['createaccount'] = false;
    # Allow sysops to review the queue
    $wgGroupPermissions['sysop']['invitesignup'] = true;
    # Suggest / add invited people to confirmed
    $wgISGroups = [ 'confirmed' ];
}

// ConfirmAccount
if( $wikiInfo->getSetting('wwExtEnableConfirmAccount') ) {
    $wgMakeUserPageFromBio = false;
    $wgAutoWelcomeNewUsers = false;
    $wgConfirmAccountRequestFormItems = [
        'UserName'        => [ 'enabled' => true ],
        'RealName'        => [ 'enabled' => false ],
        'Biography'       => [ 'enabled' => false, 'minWords' => 50 ],
        'AreasOfInterest' => [ 'enabled' => false ],
        'CV'              => [ 'enabled' => false ],
        'Notes'           => [ 'enabled' => true ],
        'Links'           => [ 'enabled' => false ],
        'TermsOfService'  => [ 'enabled' => false ],
    ];
    $wgGroupPermissions['bureaucrat']['confirmaccount-notify'] = true;
    $wgGroupPermissions['bureaucrat']['requestips'] = false;
    $wgGroupPermissions['bureaucrat']['lookupcredentials'] = false;
    $wgGroupPermissions['*']['requestips'] = false;
    $wgGroupPermissions['*']['lookupcredentials'] = false;
    $wgHooks['PersonalUrls'][] = 'onPersonalUrlsConfirmAccount';
    function onPersonalUrlsConfirmAccount( array &$personal_urls, Title $title, SkinTemplate $skin  ) {
        // Add a link to Special:RequestAccount if a link exists for login
        if ( isset( $personal_urls['login'] ) || isset( $personal_urls['anonlogin'] ) ) {
            $personal_urls['createaccount'] = array(
                'text' => wfMessage( 'requestaccount' )->text(),
                'href' => SpecialPage::getTitleFor( 'RequestAccount' )->getFullURL()
            );
        }
        return true;
    }
}


#######################################
## --- HOOKS & MW Customizations --- ##
#######################################

// https://www.mediawiki.org/wiki/Manual:$wgFooterIcons
// Add the custom powered by icons....
// TODO the Wikibase one should be in Wikibase..
$wgFooterIcons = [
    "copyright" => [
        "copyright" => [], // placeholder for the built in copyright icon
    ],
    "poweredby" => [
        "wbstack" => [
            "src" => "https://storage.googleapis.com/wbstack-static/assets/Powered_by_WBStack_88x31.png",
            "url" => "https://wbstack.com/",
            "alt" => "Powered by WBStack",
        ],
        "wikibase" => [
            "src" => "https://storage.googleapis.com/wbstack-static/assets/Powered_by_Wikibase_88x31.png",
            "url" => "https://wikiba.se/",
            "alt" => "Powered by Wikibase",
        ],
        "mediawiki" => [
            "src" => "https://storage.googleapis.com/wbstack-static/assets/mediawiki/poweredby_mediawiki_88x31.png",
            "url" => "https://www.mediawiki.org/",
            "alt" => "Powered by MediaWiki",
        ]
    ],
];

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
