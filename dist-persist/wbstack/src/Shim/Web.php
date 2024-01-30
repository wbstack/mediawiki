<?php

require_once __DIR__ . '/../loadShim.php';

// Try and get the wiki info or fail
try {
    \WBStack\Info\GlobalSet::forDomain($_SERVER['SERVER_NAME']);
} catch (\WBStack\Info\GlobalSetException $ex) {
    http_response_code($ex->getCode());
    echo "You have requested the domain: " . $_SERVER['SERVER_NAME'] . ". But that wiki can not currently be loaded.".PHP_EOL;
    if ($ex->getCode() === 404) {
        echo "It may never have existed or it might now be deleted.".PHP_EOL;
    } else {
        echo "There was a server error in the platform API.".PHP_EOL;
    }
    echo $ex->getMessage();
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
