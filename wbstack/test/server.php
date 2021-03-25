<?php

if( $_SERVER['SERVER_NAME'] !== 'localhost' ){
    die( 'This is only meant for testing from localhost' );
}

if( $_GET['domain'] !== 'localhost' ){
    die( 'Requested domain as a param must be localhost' );
}

echo file_get_contents( __DIR__ . '/../data/WikiInfo-local.json' );
