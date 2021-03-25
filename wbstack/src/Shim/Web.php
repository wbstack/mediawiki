<?php

require_once __DIR__ . '/../loadShim.php';

// Try and get the wiki info, for fail with a 404 web page
if(!\WBStack\Info\GlobalSet::forDomain( $_SERVER['SERVER_NAME'] )) {
    http_response_code(404);
    echo "You have requested the domain: " . $_SERVER['SERVER_NAME'] . ". But that wiki can not currently be loaded.\n";
    echo "It may never have existed, or it might now be deleted.\n";
    die();
}