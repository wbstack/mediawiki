<?php

// Create new versions of various files we need to add PHP to the start of
// Hijacking entrypoints (for now)
// WIll probably do something different in the future (and do this in some other service)

// Config
$mapOfChanges = [
    # TODO thumb?
    'maintenance/doMaintenance.php' => "require_once __DIR__ . '/../wikWikiInfoOrFailCli.php';",
    'api.php' => "require_once __DIR__ . '/wikWikiInfoOrFail.php';",
    'rest.php' => "require_once __DIR__ . '/wikWikiInfoOrFail.php';",
    'index.php' => "require_once __DIR__ . '/wikWikiInfoOrFail.php';",
    'load.php' => "require_once __DIR__ . '/wikWikiInfoOrFail.php';",
];

/////////////////

foreach ( $mapOfChanges as $file => $change ) {
    echo "Overriding $file\n";
    $location = __DIR__ . '/../' . $file;
    $content = file_get_contents( $location );
    $content = str_replace( "<?php", "<?php\n\n" . $change . "\n\n", $content );
    file_put_contents( $location, $content );
}
