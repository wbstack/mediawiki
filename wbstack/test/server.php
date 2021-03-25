<?php

if( $_SERVER['SERVER_NAME'] !== 'localhost' ){
    echo 'This is only meant for testing from localhost';
    die(1);
}

if( $_GET['domain'] !== 'localhost' ){
    echo 'Requested domain as a param must be localhost';
    die(1);
}

echo file_get_contents( __DIR__ . '/../data/WikiInfo-local.json' );
