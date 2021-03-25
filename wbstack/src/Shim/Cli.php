<?php

require_once __DIR__ . '/../loadShim.php';

// Only allow this file to be run via CLI
if ( php_sapi_name() !== 'cli' ) {
    die( 'Should only be run via the CLI'  . PHP_EOL );
}

// Validation of input by CLI user..
if( getenv( 'WBS_DOMAIN' ) == false ) {
    die('WBS_DOMAIN env var must be set to the domain this script should run on.' . PHP_EOL);
}

// Try and get the wiki info (from env var) or fail with a message
if(!\WBStack\Info\GlobalSet::forDomain( getenv( 'WBS_DOMAIN' ) )) {
    die('Failed to work for WBS_DOMAIN: ' . getenv( 'WBS_DOMAIN' ) . PHP_EOL);
}