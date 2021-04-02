<?php
/**
 * This file includes core settings that are unlikely to need to change much.
 * These settings should also not be needed for the localization rebuild
 */

if ( !array_key_exists( WBSTACK_INFO_GLOBAL, $GLOBALS ) ) {
	// We shouldn't reach here as all entry points should create this GLOBAL..
    // However if new things get introduced we will end up here, and we will need another shim!
    echo 'LS.php not got wiki info.';
	die(1);
}

require_once __DIR__ . '/../loadAll.php';

/** @var \WBStack\Info\WBStackInfo $wikiInfo */
$wikiInfo = $GLOBALS[WBSTACK_INFO_GLOBAL];

// Define STDERR if it is not already. This is used for logging.
if ( !defined( 'STDERR' ) ) {
    define( 'STDERR', fopen( 'php://stderr', 'w' ) );
}

// Define some conditions to switch behaviour on
$wwDomainIsMaintenance = $wikiInfo->requestDomain === 'maintenance';
$wwIsPhpUnit = isset( $maintClass ) && $maintClass === 'PHPUnitMaintClass';
$wwIsLocalisationRebuild = basename( $_SERVER['SCRIPT_NAME'] ) === 'rebuildLocalisationCache.php';

#######################################
## ---  Base MediaWiki Settings  --- ##
#######################################

// No error output or debugging in production
ini_set( 'display_errors', 0 );
$wgShowExceptionDetails = false;
if( $wwDomainIsMaintenance || $wwIsPhpUnit || $wwIsLocalisationRebuild || $wikiInfo->requestDomain === 'localhost' ) {
    ini_set( 'display_errors', 1 );
    $wgShowExceptionDetails = true;
}

// Load Logging when not in phpunit or doing l10n rebuild
if ( !$wwIsPhpUnit && !$wwIsLocalisationRebuild ) {
    $wgMWLoggerDefaultSpi = [
        'class' => \WBStack\Logging\CustomSpi::class,
        'args' => [[
            'ignoreLevels' => [
                'debug',
                'info',
            ],
            'ignoreAllInGroup' => [
                'DBPerformance',
                'objectcache',// ideally want to show objectcache errors, but not warnings
            ],
            'logAllInGroup' => [
                'WBSTACK',
                'HttpError',
                'SpamBlacklistHit',
                'security',
                'exception-json',
                //'error',
                'fatal',
                'badpass',
                'badpass-priv',
                'api-warning',
            ],
            'logAllInGroupExceptDebug' => [
                //'Wikibase',
            ],
        ]],
    ];
}

$wgServer = "https://" . $wikiInfo->domain;
$wgScriptPath = "/w";
$wgArticlePath = "/wiki/$1";

$wgDBname = $wikiInfo->wiki_db->name;
$wgDBprefix = $wikiInfo->wiki_db->prefix . '_';
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

$wgDBservers = [
    [
        'host' => getenv('MW_DB_SERVER_MASTER'),
        'dbname' => $wgDBname,
        'user' => $wikiInfo->wiki_db->user,
        'password' => $wikiInfo->wiki_db->password,
        'type' => "mysql",
        'flags' => DBO_DEFAULT,
        'load' => 1,
    ],
];

// DO NOT add the replica server config if we are just running a non wiki maint script.
// For example schema generation or localization cache reload
// WBS_DOMAIN=maint php ./w/maintenance/update.php --schema sql.sql --quick
// As in these contexts there is often no replica.... and thus one would fail...
if( !$wwDomainIsMaintenance ){
    $wgDBservers[] = [
        'host' => getenv('MW_DB_SERVER_REPLICA'),
        'dbname' => $wgDBname,
        'user' => $wikiInfo->wiki_db->user,
        'password' => $wikiInfo->wiki_db->password,
        'type' => "mysql",
        'flags' => DBO_DEFAULT,
        'max lag' => 10,
        'load' => 100,
    ];
}

// Jobs
# For now jobs will run in the requests, this obviously isn't the ideal solution and really
# there should be a job running service deployed...
# This was set to 2 as Andra experienced a backup of jobs. https://github.com/addshore/wbstack/issues/51
$wgJobRunRate = 2;

// Storage
// Compress revisions
$wgCompressRevisions = true;

// Notifications
$wgEnotifUserTalk = false;
$wgEnotifWatchlist = false;

// Files
// $wgUseImageMagick Is needed so that Score auto trims rendered musical notations
$wgUseImageMagick = true;
$wgEnableUploads = false;
$wgAllowCopyUploads = false;
$wgUseInstantCommons = false;
$wgFileExtensions = array_merge( $wgFileExtensions,
    array( 'doc', 'xls', 'mpp', 'pdf', 'ppt', 'xlsx', 'jpg',
        'tiff', 'odt', 'odg', 'ods', 'odp', 'svg'
    )
);
//$wgFileExtensions[] = 'djvu';

$wgSitename = $wikiInfo->sitename;

// Logos
$wgLogos = [
    "1x" => $wikiInfo->getSetting('wgLogo'),
];
if( $wgLogos["1x"] === null ) {
    // Fallback to the mediawiki logo without the wgLogo overlay
    $wgLogos = [
        "1x" => "https://storage.googleapis.com/wbstack-static/assets/mediawiki/mediawiki.png",
    ];
}

// Favicon
$wgFavicon = $wikiInfo->getSetting('wgFavicon');
if( $wgFavicon === null ) {
    // Default from install, but maybe we want to change this?
    $wgFavicon = "/favicon.ico";
}

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

// Language
// TODO this should be settings from the main platform
$wgLanguageCode = "en";

// Email
$wgEnableEmail = true;
$wgEnableUserEmail = false;
$wgAllowHTMLEmail = true;
// enable email authentication (confirmation) for this wiki
$wgEmailAuthentication = true;
// require email authentication
$wgEmailConfirmToEdit = true;
// TODO make this a real wbstack email address?
$wgEmergencyContact = "emergency.wbstack@addshore.com";
$wgPasswordSender = 'noreply@' . getenv('MW_EMAIL_DOMAIN');
$wgNoReplyAddress = 'noreply@' . getenv('MW_EMAIL_DOMAIN');

// Output compression needs to be disabled in 1.35 until the below phab task is fixed...
// TODO dig more to see if there is something else to do here...
// https://phabricator.wikimedia.org/T235554
$wgDisableOutputCompression  = true;

## Keys
$wgSecretKey = $wikiInfo->getSetting('wgSecretKey');
$wgAuthenticationTokenVersion = "1";

// So we are uniform, have the project namespace as Project
$wgMetaNamespace = 'Project';
// Needed so that Wikibase items appear in Special:Random and are counted as content pages.
// TODO this should be a Wikibase default behaviour?
$wgContentNamespaces[] = 120;

// TODO sort out directories and stuff...?
// $wgCacheDirectory is needed at least for the l10n rebuild
$wgCacheDirectory = '/tmp/mw-cache';
//$wgUploadDirectory = "{$IP}/images/docker/{$dockerDb}";
//$wgUploadPath = "{$wgScriptPath}/images/docker/{$dockerDb}";
//$wgTmpDirectory = "{$wgUploadDirectory}/tmp";

## Locale
/**
 * The docker image only has C.UTF-8 currently.
 * This is the default for https://www.mediawiki.org/wiki/Manual:$wgShellLocale for core as of 1.30
 * But explicitly set it here as it is all the image has!
 */
$wgShellLocale = "C.UTF-8";

#######################################
## ---   Default Permissions     --- ##
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
## ---          Skins            --- ##
#######################################
wfLoadSkin( 'Vector' );
wfLoadSkin( 'Timeless' );
wfLoadSkin( 'Modern' );
wfLoadSkin( 'MinervaNeue' );

// TODO allow turning some skins on and off?
$wgDefaultSkin = $wikiInfo->getSetting('wgDefaultSkin');
if( $wgDefaultSkin === null ) {
    // Fallback to vector
    $wgDefaultSkin = "vector";
}

#######################################
## ---        Extensions         --- ##
#######################################
wfLoadExtension( 'SyntaxHighlight_GeSHi' );
wfLoadExtension( 'RevisionSlider' );
wfLoadExtension( 'TorBlock' );
wfLoadExtension( 'Nuke' );
wfLoadExtension( 'EntitySchema' );
wfLoadExtension( 'UniversalLanguageSelector' );
wfLoadExtension( 'cldr' );
# TODO load again once there is a fix for localization cache reload without DBhttps://phabricator.wikimedia.org/T237148
#wfLoadExtension( 'Gadgets' );
wfLoadExtension( 'OAuth' );
wfLoadExtension( 'JsonConfig' );
wfLoadExtension( 'Math' );
wfLoadExtension( 'Kartographer' );
wfLoadExtension( 'PageImages' );
wfLoadExtension( 'Scribunto' );
wfLoadExtension( 'Cite' );
wfLoadExtension( 'TemplateSandbox' );
wfLoadExtension( 'WikiEditor' );
wfLoadExtension( 'CodeEditor' );
wfLoadExtension( 'CodeMirror' );
wfLoadExtension( 'SecureLinkFixer' );
wfLoadExtension( 'Echo' );
wfLoadExtension( 'Thanks' );
wfLoadExtension( 'Graph' );
wfLoadExtension( 'Poem' );
wfLoadExtension( 'TemplateData' );
wfLoadExtension( 'AdvancedSearch' );
wfLoadExtension( 'ParserFunctions' );
wfLoadExtension( 'EmbedVideo' );
wfLoadExtension( 'DeleteBatch' );
wfLoadExtension( 'MultimediaViewer' );
wfLoadExtension( 'WikiHiero' );

# ConfirmAccount (only loaded when the setting is on)
if( $wikiInfo->getSetting('wwExtEnableConfirmAccount') ) {
    require_once "$IP/extensions/ConfirmAccount/ConfirmAccount.php";
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

# InviteSignup (only loaded when the setting is on)
if( $wikiInfo->getSetting('wwExtEnableInviteSignup') ) {
    wfLoadExtension( 'InviteSignup' );
    # Restrict account creation
    $wgGroupPermissions['*']['createaccount'] = false;
    $wgGroupPermissions['user']['createaccount'] = false;
    # Allow sysops to review the queue
    $wgGroupPermissions['sysop']['invitesignup'] = true;
    # Suggest / add invited people to confirmed
    $wgISGroups = [ 'confirmed' ];
}

# WikibaseInWikitext
wfLoadExtension( 'WikibaseInWikitext' ); // custom wbstack extension
$wgWikibaseInWikitextSparqlDefaultUi = $wgServer . '/query';

# TwoColConflict
wfLoadExtension( 'TwoColConflict' );
// Enable the feature by default
$wgTwoColConflictBetaFeature = false;

# ConfirmEdit
wfLoadExtensions([ 'ConfirmEdit', 'ConfirmEdit/ReCaptchaNoCaptcha' ]);
$wgCaptchaClass = 'ReCaptchaNoCaptcha';
$wgReCaptchaSendRemoteIP = true;
$wgReCaptchaSiteKey = getenv('MW_RECAPTCHA_SITEKEY');
$wgReCaptchaSecretKey = getenv('MW_RECAPTCHA_SECRETKEY');

# Mailgun
wfLoadExtension( 'Mailgun' );
$wgMailgunAPIKey = getenv('MW_MAILGUN_API_KEY');
$wgMailgunDomain = getenv('MW_MAILGUN_DOMAIN');

# MobileFrontend
wfLoadExtension( 'MobileFrontend' );
$wgMFDefaultSkinClass = 'SkinMinerva';

# Score
wfLoadExtension( 'Score' );
$wgMusicalNotationEnableWikibaseDataType = true;

#######################################
## ---          Wikibase         --- ##
#######################################
// TODO use wfLoadExtension
require_once "$IP/extensions/Wikibase/repo/Wikibase.php";
require_once "$IP/extensions/Wikibase/repo/ExampleSettings.php";
require_once "$IP/extensions/Wikibase/client/WikibaseClient.php";
require_once "$IP/extensions/Wikibase/client/ExampleSettings.php";

// Force the concept URIs to be http (as this has always been the way on wbstack)
$wgWBRepoSettings['conceptBaseUri'] = 'http://' . $wikiInfo->domain . '/entity/';

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
        'baseUri' => $wgWBRepoSettings['conceptBaseUri'],
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

// Until we can scale redis memory we don't want to do this - https://github.com/addshore/wbstack/issues/37
$wgWBRepoSettings['sharedCacheType'] = CACHE_NONE;

# WikibaseLexeme, By default not enabled, enabled in WikiInfo-maint.json
if( $wikiInfo->getSetting('wwExtEnableWikibaseLexeme') ) {
    wfLoadExtension( 'WikibaseLexeme' );
}
# Federated Properties, By default not enabled, not enabled in maint mode
if( $wikiInfo->getSetting('wikibaseFedPropsEnable') ) {
    // This will use wikidata.org by default
    $wgWBRepoSettings['federatedPropertiesEnabled'] = true;
}

# Auth_remoteuser, By default not enabled, enabled in WikiInfo-maint.json
if( $wikiInfo->getSetting('wwSandboxAutoUserLogin') ) {
    wfLoadExtension( 'Auth_remoteuser' );
    $wgAuthRemoteuserUserName = "SandboxAdmin";
    # Allow Auth_remoteuser to create missing accounts
    $wgGroupPermissions['*']['autocreateaccount'] = true;
    # Stop users making any additional accounts
    $wgGroupPermissions['*']['createaccount'] = false;

    # Allow users to act like admins, and pretend they have confirmed emails (so no captchas)
    $wgAddGroups['user'][] = 'emailconfirmed';
    $wgAddGroups['user'][] = 'sysop';

    # Do not force people verify their email account, as they can't do that...
    $wgEmailConfirmToEdit = false;
}

# WikibaseManifest
wfLoadExtension( 'WikibaseManifest' );
$wgWbManifestExternalServiceMapping = [
    'queryservice_ui' => $wgServer . '/query',
    'queryservice' => $wgServer . '/query/sparql',
    'quickstatements' => $wgServer . '/tools/quickstatements',
];
if( $wikiInfo->getSetting('wikibaseManifestEquivEntities') ) {
    $wwEquivEntities = json_decode( $wikiInfo->getSetting('wikibaseManifestEquivEntities'), true );
    if ( is_array( $wwEquivEntities ) ) {
        $wgWbManifestWikidataEntityMapping = $wwEquivEntities;
        // Wikibase
        if ( array_key_exists( 'properties', $wwEquivEntities ) && array_key_exists( 'P1630', $wwEquivEntities['properties'] ) ) {
            $wgWBRepoSettings['formatterUrlProperty'] = $wwEquivEntities['properties']['P1630'];
        }
        if ( array_key_exists( 'properties', $wwEquivEntities ) && array_key_exists( 'P1921', $wwEquivEntities['properties'] ) ) {
            $wgWBRepoSettings['canonicalUriProperty'] = $wwEquivEntities['properties']['P1921'];
        }
        // WikibaseLexeme
        if ( array_key_exists( 'properties', $wwEquivEntities ) && array_key_exists( 'P218', $wwEquivEntities['properties'] ) ) {
            $wgLexemeLanguageCodePropertyId = $wwEquivEntities['properties']['P218'];
        }
    }
}

#######################################
## ---  l10n rebuild and beyond  --- ##
#######################################
// Skip some things when in l10n rebuild, as they complicate things.
// TODO perhaps change this so that the rebuildLocalisationCache.php passes in an ENV var to block loading the extra settings instead of detecting?

// Disable any chance of localization cache updates during web requests
$wgLocalisationCacheConf['manualRecache'] = true;

// $wgCacheDirectory setting must also be valid for the l10n cache to land on disk

if( !$wwIsLocalisationRebuild ) {
    // Only load cache settings (redis db etc) when not doing a l10n rebuild
    require_once __DIR__ . '/ProductionCache.php';

    // If we have internal settings, and have been told to load them, then load them...
    if( getenv('WBSTACK_LOAD_MW_INTERNAL') === 'yes' && file_exists( __DIR__ . '/../loadInternal.php' ) ) {
        require_once __DIR__ . '/../loadInternal.php';
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
}

require_once __DIR__ . '/Hooks.php';
