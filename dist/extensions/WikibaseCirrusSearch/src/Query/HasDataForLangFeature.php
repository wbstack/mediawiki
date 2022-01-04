<?php

namespace Wikibase\Search\Elastic\Query;

use CirrusSearch\Parser\AST\KeywordFeatureNode;
use CirrusSearch\Query\Builder\QueryBuildingContext;
use CirrusSearch\Query\FilterQueryFeature;
use CirrusSearch\Query\SimpleKeywordFeature;
use CirrusSearch\Search\SearchContext;
use CirrusSearch\WarningCollector;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;

/**
 * Abstract class supporting querying for the existence or nonexistence of term values by language.
 * Currently supports descriptions (via 'hasdescription:' or 'hascaption:') and labels (via
 * 'haslabel:').
 * @see https://phabricator.wikimedia.org/T220282
 */
class HasDataForLangFeature extends SimpleKeywordFeature implements FilterQueryFeature {

	/** @var int A limit to the number of fields that can be queried at once */
	private const MAX_FIELDS = 30;

	/** @var true[] Keyed by known language codes for set membership check */
	private $validLangs;

	/**
	 * @return string[]
	 */
	protected function getKeywords() {
		return [ 'hasdescription', 'haslabel', 'hascaption' ];
	}

	/**
	 * HasTermDataFeature constructor.
	 * @param string[] $languages list of languages indexed in elastic. Must all be lowercase.
	 */
	public function __construct( $languages ) {
		$this->validLangs = [];
		foreach ( $languages as $lang ) {
			$this->validLangs[$lang] = true;
		}
	}

	/**
	 * @param SearchContext $context
	 * @param string $key The keyword
	 * @param string $value The value attached to the keyword with quotes stripped
	 * @param string $quotedValue The original value in the search string, including quotes if used
	 * @param bool $negated Is the search negated? Not used to generate the returned AbstractQuery,
	 *  that will be negated as necessary. Used for any other building/context necessary.
	 * @return array Two element array, first an AbstractQuery or null to apply to the
	 *  query. Second a boolean indicating if the quotedValue should be kept in the search
	 *  string.
	 */
	protected function doApply( SearchContext $context, $key, $value, $quotedValue, $negated ) {
		$langCodes = $this->parseValue(
			$key,
			$value,
			$quotedValue,
			'',
			'',
			$context
		);
		if ( $langCodes === [] ) {
			$context->setResultsPossible( false );
			return [ null, false ];
		}
		return [ $this->makeQuery( $key, $langCodes ), false ];
	}

	/**
	 * Builds a boolean query requiring the existence of a value in each query language for the
	 * specified field.
	 *
	 * @param string $key the search keywords
	 * @param array $langCodes valid language codes parsed from the query term
	 * @return BoolQuery
	 */
	private function makeQuery( $key, array $langCodes ) {
		$query = new BoolQuery();
		if ( $langCodes === [ '__all__' ] ) {
			if ( $key === 'haslabel' ) {
				$field = 'labels_all.plain';
			} else {
				$field = $this->getFieldName( $key ) . '.*.plain';
			}
			$query->addShould( new Exists( $field ) );
			return $query;
		}
		foreach ( $langCodes as $lang ) {
			$query->addShould( new Exists( $this->getFieldName( $key ) . '.' . $lang . '.plain' ) );
		}
		return $query;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param string $quotedValue
	 * @param string $valueDelimiter
	 * @param string $suffix
	 * @param WarningCollector $warningCollector
	 * @return string[] deduplicated list of fields to query for the existence of values
	 */
	public function parseValue(
		$key,
		$value,
		$quotedValue,
		$valueDelimiter,
		$suffix,
		WarningCollector $warningCollector
	) {
		if ( $value === '*' ) {
			return [ '__all__' ];
		}
		$langCodes = [];

		$langCodeCandidates = array_unique( array_map( static function ( $elem ) {
			return mb_strtolower( $elem );
		}, explode( ',', $value ) ) );

		foreach ( $langCodeCandidates as $candidate ) {
			if ( isset( $this->validLangs[$candidate] ) ) {
				$langCodes[] = $candidate;
			} else {
				$warningCollector->addWarning(
					'wikibasecirrus-keywordfeature-unknown-language-code',
					$key,
					$candidate
				);
			}
		}

		if ( count( $langCodes ) > self::MAX_FIELDS ) {
			$warningCollector->addWarning( 'wikibasecirrus-keywordfeature-too-many-language-codes',
				$key, self::MAX_FIELDS, count( $langCodes ) );
			$langCodes = array_slice( $langCodes, 0, self::MAX_FIELDS );
		}

		return $langCodes;
	}

	/**
	 * @param KeywordFeatureNode $node
	 * @param QueryBuildingContext $context
	 * @return BoolQuery|null
	 */
	public function getFilterQuery( KeywordFeatureNode $node, QueryBuildingContext $context ) {
		$langCodes = $node->getParsedValue();
		if ( $langCodes === [] ) {
			return null;
		}
		return $this->makeQuery( $node->getKey(), $langCodes );
	}

	/**
	 * @param string $key the search keyword
	 * @return string field name to search
	 */
	private function getFieldName( $key ) {
		return $key === 'haslabel' ? 'labels' : 'descriptions';
	}

}
