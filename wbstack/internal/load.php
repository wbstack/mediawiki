<?php
// Loads all of the internal things in the right order..

// TODO added inter service communication auth

// Load the files
require_once __DIR__ . DIRECTORY_SEPARATOR . 'WbStackPlatformReservedUser.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'ApiWbStackInit.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'ApiWbStackOauthGet.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'ApiWbStackUpdate.php';

// Register the internal API modules
$wgAPIModules['wbstackInit'] = ApiWbStackInit::class;
$wgAPIModules['wbstackPlatformOauthGet'] = ApiWbStackOauthGet::class;
$wgAPIModules['wbstackUpdate'] = ApiWbStackUpdate::class;
