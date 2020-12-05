<?php

namespace AdvancedSearch;

use ResourceLoader;
use ResourceLoaderContext;
use ResourceLoaderModule;
use Xml;

/**
 * ResourceLoader module providing the users "searchnamespace" token.
 */
class SearchnamespaceTokenModule extends ResourceLoaderModule {

	protected $origin = self::ORIGIN_CORE_INDIVIDUAL;

	protected $targets = [ 'desktop', 'mobile' ];

	/**
	 * @param ResourceLoaderContext $context
	 *
	 * @return string JavaScript code
	 */
	public function getScript( ResourceLoaderContext $context ) {
		$user = $context->getUserObj();
		// Use FILTER_NOMIN annotation to prevent needless minification and caching (T84960).
		return ResourceLoader::FILTER_NOMIN .
			Xml::encodeJsCall(
				'mw.user.tokens.set',
				[ 'searchnamespaceToken', $user->getEditToken( 'searchnamespace' ) ],
				ResourceLoader::inDebugMode()
			);
	}

	/**
	 * @return bool
	 */
	public function supportsURLLoading() {
		return false;
	}

	/**
	 * @return string
	 */
	public function getGroup() {
		// Private modules can not be loaded as a dependency, only via OutputPage::addModules().
		return 'private';
	}

}
