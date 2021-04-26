<?php

// Create new versions of various files we need to add PHP shims to the start of
// Hijacking entrypoints (for now)
// Will probably do something different in the future (and do this in some other service)

// Config
$webShim = "require_once __DIR__ . '/wbstack/src/Shim/Web.php';";
$mapOfChanges = [
    # Main maintenance entrypoint
    'maintenance/doMaintenance.php' => "require_once __DIR__ . '/../wbstack/src/Shim/Cli.php';",
    # Main web entrypoint
    'index.php' => $webShim,
    # API entrypoints
    'api.php' => $webShim,
    'rest.php' => $webShim,
    # Utility web entrypoints
    'load.php' => $webShim,
    'opensearch_desc.php' => $webShim,
    'thumb.php' => $webShim,
    'img_auth.php' => $webShim,
];

/////////////////

foreach ( $mapOfChanges as $file => $change ) {
    echo "Overriding $file\n";
    $location = __DIR__ . '/../../' . $file;
    $content = file_get_contents( $location );
    $content = str_replace( "<?php", "<?php\n\n" . $change . "\n\n", $content );
    file_put_contents( $location, $content );
}
