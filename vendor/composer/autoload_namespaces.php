<?php

// autoload_namespaces.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'ValueParsers\\' => array($vendorDir . '/data-values/number/src', $vendorDir . '/data-values/time/src'),
    'ValueFormatters\\' => array($vendorDir . '/data-values/number/src', $vendorDir . '/data-values/time/src'),
    'TextCat' => array($vendorDir . '/wikimedia/textcat/src'),
    'Net' => array($vendorDir . '/pear/net_smtp', $vendorDir . '/pear/net_socket'),
    'Mail' => array($vendorDir . '/pear/mail', $vendorDir . '/pear/mail_mime'),
    'Liuggio' => array($vendorDir . '/liuggio/statsd-php-client/src'),
    'Less' => array($vendorDir . '/wikimedia/less.php/lib'),
    'DataValues\\' => array($vendorDir . '/data-values/number/src', $vendorDir . '/data-values/time/src'),
    'Console' => array($vendorDir . '/pear/console_getopt'),
    'ComposerVendorHtaccessCreator' => array($baseDir . '/includes/composer'),
    'ComposerPhpunitXmlCoverageEdit' => array($baseDir . '/includes/composer'),
    'ComposerHookHandler' => array($baseDir . '/includes/composer'),
    'CSSMin' => array($vendorDir . '/wikimedia/minify/src'),
    '' => array($vendorDir . '/cssjanus/cssjanus/src', $vendorDir . '/pear/pear-core-minimal/src'),
);
