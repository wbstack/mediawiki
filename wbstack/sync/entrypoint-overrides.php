<?php

// Create new versions of various files we need to add PHP shims to the start of
// Hijacking entrypoints (for now)
// Will probably do something different in the future (and do this in some other service)

// Config
$webShim = "require_once __DIR__ . '/wbstack/src/Shim/Web.php';";
$mapOfChanges = [
    # TODO thumb?
    'maintenance/doMaintenance.php' => "require_once __DIR__ . '/../wbstack/src/Shim/Cli.php';",
    'api.php' => $webShim,
    'rest.php' => $webShim,
    'index.php' => $webShim,
    'load.php' => $webShim,
];

/////////////////

foreach ( $mapOfChanges as $file => $change ) {
    echo "Overriding $file\n";
    $location = __DIR__ . '/../../' . $file;
    $content = file_get_contents( $location );
    $content = str_replace( "<?php", "<?php\n\n" . $change . "\n\n", $content );
    file_put_contents( $location, $content );
}
