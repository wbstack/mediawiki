<?php

// Know about proxies... so that we get the real IP..
$wgCdnServersNoPurge = [
    getenv('MW_ALLOWED_PROXY_CIDR')
];

// This one is needed prior to 1.34
// TODO this can probably be removed now?
$wgSquidServersNoPurge = $wgCdnServersNoPurge;

$wgParserCacheType = 'db-replicated'; // 'db-replicated' is defined in LocalSettings.php currently

// Don't specify a redis cache when running dbless maint script
// TODO we probably do want a redis connection in some maint scripts...
if(!$wwDomainIsMaintenance) {
    /** @see RedisBagOStuff for a full explanation of these options. **/
    $wgMainCacheType = 'redis';
    $wgSessionCacheType = 'redis';
    $wgObjectCaches['redis'] = [
        'class' => 'ReplicatedBagOStuff',
        'readFactory' => [
            'factory' => [ 'ObjectCache', 'newFromParams' ],
            'args'  => [ [
                'class' => 'RedisBagOStuff',
                'servers' => [ getenv('MW_REDIS_SERVER_READ') ]
            ] ]
        ],
        'writeFactory' => [
            'factory' => [ 'ObjectCache', 'newFromParams' ],
            'args'  => [ [
                'class' => 'RedisBagOStuff',
                'servers' => [ getenv('MW_REDIS_SERVER_WRITE') ]
            ] ]
        ],
        'loggroup'  => 'RedisBagOStuff',
        'reportDupes' => false
    ];
    if(getenv('MW_REDIS_PASSWORD') !== '') {
        // Only set the password if not empty
        // TODO do this optional password setting in a less evil way...
        $wgObjectCaches['redis']['readFactory']['args'][0]['password'] = getenv('MW_REDIS_PASSWORD');
        $wgObjectCaches['redis']['writeFactory']['args'][0]['password'] = getenv('MW_REDIS_PASSWORD');
    }
}

// Modified default from https://www.mediawiki.org/wiki/Manual:$wgObjectCaches
// to have slightly more aggressive cache purging
$wgObjectCaches[CACHE_DB] = [
    'class' => SqlBagOStuff::class,
    'loggroup' => 'SQLBagOStuff',
    'args'  => [ [
        'purgePeriod' => 5,
        'purgeLimit' => 1000,
    ] ]
];
$wgObjectCaches['db-replicated'] = [
    'class'       => ReplicatedBagOStuff::class,
    'readFactory' => [
        'factory' => [ 'ObjectCache', 'newFromParams' ],
        'args'  => [ [
            'class' => SqlBagOStuff::class,
            'replicaOnly' => true,
            'purgePeriod' => 5,
            'purgeLimit' => 1000,
        ] ]
    ],
    'writeFactory' => [
        'factory' => [ 'ObjectCache', 'newFromParams' ],
        'args'  => [ [
            'class' => SqlBagOStuff::class,
            'replicaOnly' => false,
            'purgePeriod' => 5,
            'purgeLimit' => 1000,
        ] ]
    ],
    'loggroup'  => 'SQLBagOStuff',
    'reportDupes' => false
];
