<?php

namespace AdvancedSearch;

use MediaWiki\Html\Html;
use MediaWiki\ResourceLoader as RL;
use MediaWiki\ResourceLoader\ResourceLoader;

/**
 * ResourceLoader module providing the users "searchnamespace" token.
 */
class SearchnamespaceTokenModule extends RL\Module {

	/** @inheritDoc */
	protected $origin = self::ORIGIN_CORE_INDIVIDUAL;

	/** @inheritDoc */
	public function getScript( RL\Context $context ) {
		$user = $context->getUserObj();
		// Use FILTER_NOMIN annotation to prevent needless minification and caching (T84960).
		return ResourceLoader::FILTER_NOMIN .
			Html::encodeJsCall(
				'mw.user.tokens.set',
				[ 'searchnamespaceToken', $user->getEditToken( 'searchnamespace' ) ],
				(bool)$context->getDebug()
			);
	}

	/** @inheritDoc */
	public function supportsURLLoading() {
		return false;
	}

	/** @inheritDoc */
	public function getGroup() {
		// Private modules can not be loaded as a dependency, only via OutputPage::addModules().
		return 'private';
	}

}
