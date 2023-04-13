<?php

namespace TwoColConflict;

use ExtensionRegistry;
use MediaWiki\MediaWikiServices;

return [

	'TwoColConflictContext' => static function ( MediaWikiServices $services ) {
		$extensionRegistry = ExtensionRegistry::getInstance();
		$mobileContext = $extensionRegistry->isLoaded( 'MobileFrontend' )
			? $services->getService( 'MobileFrontend.Context' )
			: null;

		return new TwoColConflictContext(
			$services->getMainConfig(),
			$services->getUserOptionsLookup(),
			$extensionRegistry,
			$mobileContext
		);
	},

];
