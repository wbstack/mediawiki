<?php

if( $_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== 'api.svc' ){
    echo 'This is only meant for testing from localhost';
    die(1);
}

$matches = [];
$domainIsLocalHost = preg_match("/(\w+)\.(localhost)/", $_GET['domain'], $matches) === 1;
if( !$domainIsLocalHost ){
    echo 'Requested domain as a param must be subdomain of localhost';
    die(1);
    
}

// subdomain is 1 element
$subdomain = $matches[1];

if ( $subdomain === 'failwith500' ) {
    http_response_code(500);
    echo "Internal server error";
    die(1);
}

$file = __DIR__ . '/WikiInfo-'.$subdomain.'.json';
if ( !file_exists($file) ) {
    http_response_code(404);
    echo 'Requested subdomain does not exist in test data';
    die(1);
}

echo file_get_contents( $file );
