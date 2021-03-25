<?php

// Updates the Dockerfile with newer hashes

// Config
$releaseBranch = 'REL1_35';

/////////////////

// A github access token is needed to access the github API
if( !file_exists( __DIR__ . '/.github' ) ) {
    die('.github file with personal access token for public_repo must exist. eg. eu21yh0fj10f');
}
$getGithubApiUrl = function ( $repoName ) use ( $releaseBranch ) {
    return 'https://api.github.com/repos/wikimedia/' . $repoName . '/commits/' . $releaseBranch;
};

// We need some regexes to extract things from the 02-fetch.sh file
$getRegexForRepo = function( $repoName ) {
    return '/(https:\/\/codeload\.github\.com\/wikimedia\/)(' . $repoName . ')(\/zip\/)([a-z0-9]+)/m';
};
$regexMatchingAllRepos = '/(https:\/\/codeload\.github\.com\/wikimedia\/)(mediawiki-(skins|extensions)-[^\/]+)(\/zip\/)([a-z0-9]+)/m';

// These methods actually calls the Github API
$getCommits = function ( $repoName ) use ( $getGithubApiUrl ) {
    $url = $getGithubApiUrl( $repoName );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_USERAGENT, "wbstack.com - mediawiki code version updater" );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: token ' . trim(file_get_contents(__DIR__ .'/.github')),
    ));
    // XXX: the 2 below are evil..... but this is not so critical and right now i just want it to work...
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    if($output === false) {
        echo "CURL ERROR: " . curl_error($ch) . "\n";
        exit(1);
    }
    curl_close($ch);
    return json_decode( $output, true );
};
$getLatestCommitHash = function ( $repoName ) use ( $getCommits ) {
    $commits = $getCommits( $repoName );
    if(!array_key_exists( 'sha', $commits )) {
        var_dump($commits);
        return false;
    }
    return $commits['sha'];
};

// This method updates the 02-fetch.sh file
$updateCommitInDockerfile = function ( $repoName, $fetchScript ) use ( $getLatestCommitHash, $getRegexForRepo ) {
    $newHash = $getLatestCommitHash( $repoName );
    if(!$newHash){
        return $fetchScript;
    }
    return preg_replace( $getRegexForRepo( $repoName ), '${1}${2}${3}' . $newHash, $fetchScript );
};

echo "Running for branch {$releaseBranch}" . PHP_EOL;

$fetchScript = file_get_contents( __DIR__ . '/02-fetch.sh' );

preg_match_all( $regexMatchingAllRepos, $fetchScript, $matches );
$repoNames = $matches[2];

foreach( $repoNames as $repoName ) {
    if( $repoName === 'mediawiki-extensions-EntitySchema' ) {
        echo "Skipping " .  $repoName . PHP_EOL;
        continue;
    }
    echo "Updating for {$repoName}" . PHP_EOL;
    $fetchScript = $updateCommitInDockerfile( $repoName, $fetchScript );
}

$result = file_put_contents( __DIR__ . '/02-fetch.sh', $fetchScript );
echo "FILE SAVED!" . PHP_EOL;

echo "REMEMBER: things like mediawiki.git are not updated by this script!" . PHP_EOL;
