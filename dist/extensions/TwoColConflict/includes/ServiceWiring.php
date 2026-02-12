<?php

namespace TwoColConflict;

use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;

return [

	'TwoColConflictContext' => static function ( MediaWikiServices $services ): TwoColConflictContext {
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
