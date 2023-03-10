<?php

use MediaWiki\MediaWikiServices;
use Wikibase\Client\WikibaseClient;

// phpcs:disable Squiz.Functions.GlobalFunction.Found
// phpcs:disable MediaWiki.NamingConventions.PrefixedGlobalFunctions.wfPrefix

if ( !function_exists( 'mws' ) ) {
	// @phan-suppress-next-line PhanRedefineFunction guarded by function_exists()
	function mws() {
		return MediaWikiServices::getInstance();
	}
}

class_alias( WikibaseClient::class, 'wbc' );
