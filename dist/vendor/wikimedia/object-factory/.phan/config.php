<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['directory_list'][] = 'vendor/psr/container';
$cfg['exclude_analysis_directory_list'][] = 'vendor/';

return $cfg;
