<?php

require_once __DIR__ . '/WikWiki.php';

// Check the site being accessed exists and set the data into a global
call_user_func(function () {
    $success = WikWiki::setGlobalForRequestDomain( $_SERVER['SERVER_NAME'] );
    if(!$success) {
        http_response_code(404);
        echo "You have requested the domain: " . $_SERVER['SERVER_NAME'] . ". But that wiki can not currently be loaded.\n";
        echo "It may never have existed, or it might now be deleted.\n";
        die();
    }

});
