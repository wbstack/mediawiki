<?php

namespace TwoColConflict;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use TwoColConflict\Logging\ThreeWayMerge;

return [

	'TwoColConflictContext' => function ( MediaWikiServices $services ) {
		$extensionRegistry = ExtensionRegistry::getInstance();
		$mobileContext = $extensionRegistry->isLoaded( 'MobileFrontend' )
			? $services->getService( 'MobileFrontend.Context' )
			: null;

		return new TwoColConflictContext(
			MediaWikiServices::getInstance()->getMainConfig(),
			$extensionRegistry,
			$mobileContext
		);
	},

	'TwoColConflictThreeWayMerge' => function ( MediaWikiServices $services ) {
		return new ThreeWayMerge();
	},

];
