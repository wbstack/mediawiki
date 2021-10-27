<?php
namespace Wikibase\Search\Elastic;

use CirrusSearch\Search\BaseCirrusSearchResultSet;
use Wikibase\Lib\TermLanguageFallbackChain;

/**
 * Result set for entity search
 */
class EntityResultSet extends BaseCirrusSearchResultSet {

	/**
	 * Display fallback chain.
	 * @var TermLanguageFallbackChain
	 */
	private $termFallbackChain;
	/**
	 * Display language code
	 * @var string
	 */
	private $displayLanguage;

	/**
	 * @var \Elastica\ResultSet|null
	 */
	private $result;

	/**
	 * @param string $displayLanguage
	 * @param TermLanguageFallbackChain $displayFallbackChain
	 * @param \Elastica\ResultSet $result
	 */
	public function __construct( $displayLanguage,
		TermLanguageFallbackChain $displayFallbackChain,
		\Elastica\ResultSet $result
	) {
		$this->result = $result;
		$this->termFallbackChain = $displayFallbackChain;
		$this->displayLanguage = $displayLanguage;
	}

	protected function transformOneResult( \Elastica\Result $result ) {
		return new EntityResult( $this->displayLanguage, $this->termFallbackChain, $result );
	}

	/**
	 * @return \Elastica\ResultSet|null
	 */
	public function getElasticaResultSet() {
		return $this->result;
	}

	/**
	 * Did the search contain search syntax?  If so, Special:Search won't offer
	 * the user a link to a create a page named by the search string because the
	 * name would contain the search syntax.
	 * @return bool
	 */
	public function searchContainedSyntax() {
		return false;
	}

}
