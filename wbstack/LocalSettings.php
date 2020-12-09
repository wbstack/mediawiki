<?php
/**
 * This file includes core settings that are unlikely to need to change much.
 * These settings should also not be needed for the localization rebuild
 */

if ( !array_key_exists( WIKWIKI_GLOBAL, $GLOBALS ) ) {
	// We shouldn't reach here as all entry points should create this GLOBAL..
    // However if new things get introduced we will end up here.
	die('LS not got wiki info.');
}

/** @var WikWiki $wikWiki */
$wikWiki = $GLOBALS[WIKWIKI_GLOBAL];

// Define STDERR if it is not already. This is used for logging.
if ( !defined( 'STDERR' ) ) {
    define( 'STDERR', fopen( 'php://stderr', 'w' ) );
}

// Define some decision making pointers
$wwDomainSaysLocal = substr($wikWiki->requestDomain,-9, 9) === 'localhost';
$wwDomainSaysMaint = $wikWiki->requestDomain === 'maint' || $wikWiki->requestDomain === 'maintenance'; // TODO probably only need to check one of these..
$wwIsInPhpUnit = isset( $maintClass ) && $maintClass === 'PHPUnitMaintClass';
$wwIsInLocalisationRebuild = basename( $_SERVER['SCRIPT_NAME'] ) === 'rebuildLocalisationCache.php';

// Show errors when being localhost or when in a maint script
if( $wwDomainSaysLocal || $wwDomainSaysMaint ) {
    ini_set( 'display_errors', 1 );
    $wgShowExceptionDetails = true;
} else {
    ini_set( 'display_errors', 0 );
    $wgShowExceptionDetails = false;
}

// Load Logging stuff if we are not running in phpunit
if ( !$wwIsInPhpUnit && !$wwIsInLocalisationRebuild ) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'WikWikiSpi.php';
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'WikWikiLogger.php';
    $wgMWLoggerDefaultSpi = [
        'class' => \WikWikiSpi::class,
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

if( $wwDomainSaysLocal ) {
	// TODO this code path shouldn't be accessible when in PROD
	// TODO fix totally hardcoded port for dev us
	$wgServer = "//" . $wikWiki->requestDomain . ":8083";
	// Internal is on 8073...
	if(getenv('WBSTACK_LOAD_MW_INTERNAL') === 'yes' && file_exists( __DIR__ . '/InternalSettings.php' )){
        $wgServer = "//" . $wikWiki->requestDomain . ":8073";
    }
} else {
	$wgServer = "//" . $wikWiki->domain;
}

$wgDBname = $wikWiki->wiki_db->name;

$wgDBservers = [
    [
        'host' => getenv('MW_DB_SERVER_MASTER'),
        'dbname' => $wgDBname,
        'user' => $wikWiki->wiki_db->user,
        'password' => $wikWiki->wiki_db->password,
        'type' => "mysql",
        'flags' => DBO_DEFAULT,
        'load' => 1,
    ],
];

// DO NOT add the replica server config if we are just running a non wiki maint script.
// For example schema generation or localization cache reload
// WW_DOMAIN=maint php ./w/maintenance/update.php --schema sql.sql --quick
// As in these contexts there is often no replica.... and thus one would fail...
if( !$wwDomainSaysMaint ){
    $wgDBservers[] = [
        'host' => getenv('MW_DB_SERVER_REPLICA'),
        'dbname' => $wgDBname,
        'user' => $wikWiki->wiki_db->user,
        'password' => $wikWiki->wiki_db->password,
        'type' => "mysql",
        'flags' => DBO_DEFAULT,
        'max lag' => 10,
        'load' => 100,
    ];
}

// Output compression needs to be disabled in 1.35 until the below phab task is fixed...
// TODO dig more to see if there is something else to do here...
// https://phabricator.wikimedia.org/T235554
$wgDisableOutputCompression  = true;

$wgDBprefix = $wikWiki->wiki_db->prefix . '_';
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

## Keys
$wgAuthenticationTokenVersion = "1";

// TODO no idea if this is right?
$wgScriptPath = "/w";
$wgArticlePath = "/wiki/$1";

// So we are uniform, have the project namespace as Project
$wgMetaNamespace = 'Project';

// TODO custom favicons
$wgFavicon = "{$wgScriptPath}/favicon.ico";

// TODO sort out directories and stuff...?
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

## --- CACHING ---
$wgCacheDirectory = '/tmp/mw-cache';

//  Set this to true to disable cache updates on web requests.
$wgLocalisationCacheConf['manualRecache'] = true;

## --- SKINS ---
wfLoadSkin( 'Vector' );
wfLoadSkin( 'Timeless' );
wfLoadSkin( 'Modern' );
wfLoadSkin( 'MinervaNeue' );

## --- EXTENSIONS ---
wfLoadExtension( 'SyntaxHighlight_GeSHi' );
wfLoadExtension( 'RevisionSlider' );
wfLoadExtension( 'Mailgun' );
wfLoadExtension( 'TorBlock' );
wfLoadExtension( 'Nuke' );
wfLoadExtensions([ 'ConfirmEdit', 'ConfirmEdit/ReCaptchaNoCaptcha' ]);
wfLoadExtension( 'WikibaseInWikitext' ); // custom wbstack extension
wfLoadExtension( 'EntitySchema' );
wfLoadExtension( 'UniversalLanguageSelector' );
wfLoadExtension( 'cldr' );
# TODO load again once there is a fix for localization cache reload without DBhttps://phabricator.wikimedia.org/T237148
#wfLoadExtension( 'Gadgets' );
wfLoadExtension( 'TwoColConflict' );
wfLoadExtension( 'OAuth' );
wfLoadExtension( 'JsonConfig' );
wfLoadExtension( 'Score' );
wfLoadExtension( 'Math' );
wfLoadExtension( 'Kartographer' );
wfLoadExtension( 'PageImages' );
wfLoadExtension( 'Scribunto' );
wfLoadExtension( 'Cite' );
wfLoadExtension( 'TemplateSandbox' );
wfLoadExtension( 'WikiEditor' );
wfLoadExtension( 'CodeEditor' );
wfLoadExtension( 'SecureLinkFixer' );
wfLoadExtension( 'Echo' );
wfLoadExtension( 'Thanks' );
wfLoadExtension( 'Graph' );
wfLoadExtension( 'Poem' );
wfLoadExtension( 'TemplateData' );
wfLoadExtension( 'AdvancedSearch' );
wfLoadExtension( 'ParserFunctions' );
wfLoadExtension( 'EmbedVideo' );
wfLoadExtension( 'MobileFrontend' );
wfLoadExtension( 'DeleteBatch' );
wfLoadExtension( 'MultimediaViewer' );
if( $wikWiki->getSetting('wwExtEnableInviteSignup') ) {
    wfLoadExtension( 'InviteSignup' );
}
if( $wikWiki->getSetting('wwExtEnableConfirmAccount') ) {
    require_once "$IP/extensions/ConfirmAccount/ConfirmAccount.php";
}
# TODO configure
#wfLoadExtension( 'Elastica' );
#require_once "$IP/extensions/CirrusSearch/CirrusSearch.php";

# Wikibase
require_once "$IP/extensions/Wikibase/repo/Wikibase.php";
require_once "$IP/extensions/Wikibase/repo/ExampleSettings.php";
require_once "$IP/extensions/Wikibase/client/WikibaseClient.php";
require_once "$IP/extensions/Wikibase/client/ExampleSettings.php";
# WikibaseLexeme, By default not enabled, enabled in maintWikWiki.json
if( $wikWiki->getSetting('wwExtEnableWikibaseLexeme') ) {
    wfLoadExtension( 'WikibaseLexeme' );
}

# Auth_remoteuser, By default not enabled, enabled in maintWikWiki.json
if( $wikWiki->getSetting('wwSandboxAutoUserLogin') ) {
    wfLoadExtension( 'Auth_remoteuser' );
    $wgAuthRemoteuserUserName = "SandboxAdmin";
}

// Load the extra settings!
// Only when not doing rebuildLocalisationCache.php (done at build time) as this file will not exist then...
// TODO perhaps change this so that the rebuildLocalisationCache.php passes in an ENV var to block loading the extra settings instead?
if( !$wwIsInLocalisationRebuild ) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'FinalSettings.php';
}
