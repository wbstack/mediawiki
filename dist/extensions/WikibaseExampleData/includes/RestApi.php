<?php

namespace MediaWiki\Extension\WikibaseExampleData;

use MediaWiki\Extension\WikibaseExampleData\DataLoader;
use MediaWiki\Rest\SimpleHandler;

class RestApi extends SimpleHandler {

	public function run() {
		// Timeout of 10 mins, as we generally want this to succeed even if this are a bit slow?
        @set_time_limit( 60*10 );

		$loader = new DataLoader();
		$loader->execute();

		return "Done!";
	}
}
