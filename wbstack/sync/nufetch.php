<?php

function downloadFile($url, $path) {
    $ch = curl_init($url);
    $fp = fopen($path, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}

function extractFileOfUnknownType($file, $destination) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file);
    finfo_close($finfo);
    switch($mime) {
        case 'application/zip':
            extractZipFile($file, $destination);
            break;
        default:
            throw new Exception('Unknown file type: ' . $mime);
    }
}

function extractZipFile($file, $destination) {
    $zip = new ZipArchive();
    $zip->open($file);
    $zip->extractTo($destination);
    $zip->close();
}

function recursiveCopy($source, $destination) {
    foreach(glob($source . '/*') as $file) {
        if(is_dir($file)) {
            mkdir($destination . '/' . basename($file), 0777, true);
            recursiveCopy($file, $destination . '/' . basename($file));
        } else {
            copy($file, $destination . '/' . basename($file));
        }
    }
}

$codebases = json_decode(file_get_contents(__DIR__ . '/nufetch.json' ), true);
$temporaryDirectory = __DIR__ . '/.tmp/';

// TODO make async
foreach( $codebases as $codebase ) {
    $name = $codebase['name'];
    $temporaryFile = $temporaryDirectory . $name;
    downloadFile($codebase['artifactUrl'], $temporaryFile);
    $temporaryExtraction = $temporaryDirectory . $name . '-extracted';
    extractFileOfUnknownType($temporaryFile, $temporaryExtraction);
}

// TODO complile the downloaded and extracted things into a build folder
foreach( $codebases as $codebase ) {
    // TODO build the codebase into a different directory, but for now use the root of the repo
    $repoRoot = __DIR__ . '/../../';
    $name = $codebase['name'];
    $artifactLevel = $codebase['artifactLevel'];
    $temporaryExtraction = $temporaryDirectory . $name . '-extracted';
    $source = $temporaryExtraction . '/' . str_repeat('*/', $artifactLevel);
    recursiveCopy($source, $repoRoot);
}