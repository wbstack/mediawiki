<?php

// Updates the Dockerfile with newer hashes

// Config
$releaseBranch = 'REL1_35';

/////////////////

if( !file_exists( __DIR__ . '/.github' ) ) {
    die('.github file with personal access token for public_repo must exist. eg. addshore:eu21yh0fj10f');
}

$getRegexForRepo = function( $repoName ) {
    return '/^(ADD https:\/\/github.com\/wikimedia\/)(' . $repoName . ')(\/archive\/)([a-z0-9]+)(\.zip \.)$/m';
};

$getRegexMatchingAllRepos = function() {
    return '/^(ADD https:\/\/github.com\/wikimedia\/)(mediawiki-(skins|extensions)-[^\/]+)(\/archive\/)([a-z0-9]+)(\.zip \.)$/m';
};

$getGithubApiUrl = function ( $repoName ) use ( $releaseBranch ) {
    return 'https://api.github.com/repos/wikimedia/' . $repoName . '/commits/' . $releaseBranch;
};

$getCommits = function ( $repoName ) use ( $getGithubApiUrl ) {
    $url = $getGithubApiUrl( $repoName );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_USERAGENT, "wbstack.com - mediawiki docker file updater" );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: token ' . trim(json_decode(file_get_contents(__DIR__ .'/.github'))),
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
        exit(1);
    }
    return $commits['sha'];
};

$updateCommitInDockerfile = function ( $repoName, $df ) use ( $getLatestCommitHash, $getRegexForRepo ) {
    $newHash = $getLatestCommitHash( $repoName );
    return preg_replace( $getRegexForRepo( $repoName ), '${1}${2}${3}' . $newHash . '${5}', $df );
};

echo "Running for branch {$releaseBranch}" . PHP_EOL;

$df = file_get_contents( __DIR__ . '/Dockerfile' );

preg_match_all( $getRegexMatchingAllRepos(), $df, $matches );
$repoNames = $matches[2];

foreach( $repoNames as $repoName ) {
    if( $repoName === 'mediawiki-extensions-EntitySchema' ) {
        echo "Skipping " .  $repoName . PHP_EOL;
        continue;
    }
    echo "Updating for {$repoName}" . PHP_EOL;
    $df = $updateCommitInDockerfile( $repoName, $df );
}

$result = file_put_contents( __DIR__ . '/Dockerfile', $df );
