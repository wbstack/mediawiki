<?php

require_once __DIR__ . '/WikWiki.php';

// Only allow this file to be run via CLI
if ( php_sapi_name() !== 'cli' ) {
    die( 'Should only be run via the CLI'  . PHP_EOL );
}

// Validation of input by CLI user..
if( getenv( 'WW_DOMAIN' ) == false ) {
    die('WW_DOMAIN env var must be set to the domain this script should run on.' . PHP_EOL);
}

// Check the site being accessed exists and set the data into a global
call_user_func(function () {
    $success = WikWiki::setGlobalForRequestDomain( getenv( 'WW_DOMAIN' ) );
    if(!$success) {
        die('Failed to work for WW_DOMAIN: ' . getenv( 'WW_DOMAIN' ) . PHP_EOL);
    }

});
