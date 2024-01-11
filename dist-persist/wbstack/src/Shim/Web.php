<?php

require_once __DIR__ . '/../loadShim.php';

// Try and get the wiki info or fail
try {
    \WBStack\Info\GlobalSet::forDomain($_SERVER['SERVER_NAME']);
} catch (Exception $ex) {
    http_response_code($ex->getCode());
    echo "You have requested the domain: " . $_SERVER['SERVER_NAME'] . ". But that wiki can not currently be loaded.\n";
    echo "It may never have existed, it might now be deleted, or there was a server error.\n";
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
