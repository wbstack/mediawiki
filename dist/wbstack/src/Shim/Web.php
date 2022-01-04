<?php

require_once __DIR__ . '/../loadShim.php';

// Try and get the wiki info, for fail with a 404 web page
if(!\WBStack\Info\GlobalSet::forDomain( $_SERVER['SERVER_NAME'] ) ) {
    http_response_code(404);
    echo "You have requested the domain: " . $_SERVER['SERVER_NAME'] . ". But that wiki can not currently be loaded.\n";
    echo "It may never have existed, or it might now be deleted.\n";
    die(1);
}

// Register itnernal pre MediaWiki API endpoints
// Only load these internal API endpoints when set to internal
if( getenv('WBSTACK_LOAD_MW_INTERNAL') === 'yes' ) {
    require_once __DIR__ . '/../Internal/PreApiWbStackUpdate.php';
    if( $_GET["action"] === 'wbstackUpdate' ) {
        ( new \WBStack\Internal\PreApiWbStackUpdate() )->execute();
        exit(0);
    }
}
