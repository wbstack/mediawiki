<?php

namespace MediaWiki\Extension\WikibaseManifest;

use TitleFactory;

class TitleFactoryMainPageUrl {

	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	public function getValue(): string {
		$mainPageTitle = $this->titleFactory->newMainPage();

		return $mainPageTitle->getFullURL();
	}
}
