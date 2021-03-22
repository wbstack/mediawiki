<?php

namespace MediaWiki\Extension\WikibaseManifest;

use MediaWiki\Rest\SimpleHandler;

class RestApi extends SimpleHandler {
	private $generator;
	private $emptyValueCleaner;

	public function __construct(
		ManifestGenerator $generator,
		EmptyValueCleaner $emptyValueCleaner
	) {
		$this->generator = $generator;
		$this->emptyValueCleaner = $emptyValueCleaner;
	}

	public function run() {
		$output = $this->generator->generate();
		return $this->emptyValueCleaner->omitEmptyValues( $output );
	}
}
