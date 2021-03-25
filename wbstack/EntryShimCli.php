<?php

require_once __DIR__ . '/src/load.php';

// Only allow this file to be run via CLI
if ( php_sapi_name() !== 'cli' ) {
    die( 'Should only be run via the CLI'  . PHP_EOL );
}

// Validation of input by CLI user..
if( getenv( 'WBS_DOMAIN' ) == false ) {
    die('WBS_DOMAIN env var must be set to the domain this script should run on.' . PHP_EOL);
}

// Check the site being accessed exists and set the data into a global
call_user_func(function () {
    $success = \WBStack\Info\WBStackInfo::setGlobalForRequestDomain( getenv( 'WBS_DOMAIN' ) );
    if(!$success) {
        die('Failed to work for WBS_DOMAIN: ' . getenv( 'WBS_DOMAIN' ) . PHP_EOL);
    }

});
