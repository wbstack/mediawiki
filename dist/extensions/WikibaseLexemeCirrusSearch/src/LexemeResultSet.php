<?php
namespace Wikibase\Lexeme\Search\Elastic;

use CirrusSearch\Search\BaseCirrusSearchResultSet;
use Elastica\Result;
use Elastica\ResultSet as ElasticaResultSet;
use Language;
use Wikibase\Lexeme\DataAccess\LexemeDescription;

/**
 * Result set for Lexeme fulltext search
 */
class LexemeResultSet extends BaseCirrusSearchResultSet {
	/**
	 * @var Language
	 */
	private $displayLanguage;
	/**
	 * @var LexemeDescription
	 */
	private $descriptionMaker;
	/**
	 * Pre-processed results from Lexeme search, as raw data -
	 * not yet localized and without description generated.
	 * @var array
	 */
	private $rawResults;

	/**
	 * $rawResults indexed by hash on the originating elastica result set.
	 * @var array[]
	 */
	private $rawResultsByHash = [];

	/**
	 * @var \Elastica\ResultSet
	 */
	private $elasticaResultSet;

	/**
	 * @param ElasticaResultSet $ESresult
	 * @param Language $displayLanguage
	 * @param LexemeDescription $descriptionMaker
	 * @param array[] $lexemeResults Pre-processed data from Lexeme
	 */
	public function __construct(
		ElasticaResultSet $ESresult,
		Language $displayLanguage,
		LexemeDescription $descriptionMaker,
		array $lexemeResults
	) {
		$this->displayLanguage = $displayLanguage;
		$this->descriptionMaker = $descriptionMaker;
		$this->rawResults = $lexemeResults;
		$this->elasticaResultSet = $ESresult;
		foreach ( $lexemeResults as $raw ) {
			$this->rawResultsByHash[$raw['elastica_result_hash']] = $raw;
		}
	}

	/**
	 * @param Result $result
	 * @return LexemeResult|null
	 * @throws \MWException
	 */
	protected function transformOneResult( Result $result ) {
		$hash = spl_object_hash( $result );
		$raw = $this->rawResultsByHash[$hash] ?? null;
		if ( $raw === null ) {
			return null;
		}
		return new LexemeResult( $this->displayLanguage, $this->descriptionMaker, $raw );
	}

	/**
	 * Get raw results.
	 * Used in testing.
	 * @return array
	 */
	public function getRawResults() {
		return $this->rawResults;
	}

	/**
	 * @return \Elastica\ResultSet|null
	 */
	public function getElasticaResultSet() {
		return $this->elasticaResultSet;
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
