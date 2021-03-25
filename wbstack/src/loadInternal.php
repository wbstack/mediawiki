<?php

# Load things that are only needed internally
# This is called from within settings

# Double check that we want to do this
if ( getenv('WBSTACK_LOAD_MW_INTERNAL') !== 'yes' ) {
    die( 'no!' );
}

// Load the files
require_once __DIR__ . '/Internal/WbStackPlatformReservedUser.php';
require_once __DIR__ . '/Internal/ApiWbStackInit.php';
require_once __DIR__ . '/Internal/ApiWbStackOauthGet.php';
require_once __DIR__ . '/Internal/ApiWbStackUpdate.php';

// Register the internal API modules
$wgAPIModules['wbstackInit'] = \WBStack\Internal\ApiWbStackInit::class;
$wgAPIModules['wbstackPlatformOauthGet'] = \WBStack\Internal\ApiWbStackOauthGet::class;
$wgAPIModules['wbstackUpdate'] = \WBStack\Internal\ApiWbStackUpdate::class;

// This is needed for Sandbox sites to have their Example data loaded via API
wfLoadExtension( 'WikibaseExampleData' );
