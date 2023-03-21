<?php

namespace AdvancedSearch;

use MediaWiki\ResourceLoader as RL;
use MediaWiki\ResourceLoader\ResourceLoader;
use Xml;

/**
 * ResourceLoader module providing the users "searchnamespace" token.
 */
class SearchnamespaceTokenModule extends RL\Module {

	/**
	 * @var int
	 */
	protected $origin = self::ORIGIN_CORE_INDIVIDUAL;

	/**
	 * @var string[]
	 */
	protected $targets = [ 'desktop', 'mobile' ];

	/**
	 * @param RL\Context $context
	 *
	 * @return string JavaScript code
	 */
	public function getScript( RL\Context $context ) {
		$user = $context->getUserObj();
		// Use FILTER_NOMIN annotation to prevent needless minification and caching (T84960).
		return ResourceLoader::FILTER_NOMIN .
			Xml::encodeJsCall(
				'mw.user.tokens.set',
				[ 'searchnamespaceToken', $user->getEditToken( 'searchnamespace' ) ],
				(bool)ResourceLoader::inDebugMode()
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
