<?php

namespace Wikibase\Search\Elastic\Query;

use CirrusSearch\Parser\AST\KeywordFeatureNode;
use CirrusSearch\Query\Builder\QueryBuildingContext;
use CirrusSearch\Query\FilterQueryFeature;
use CirrusSearch\Query\SimpleKeywordFeature;
use CirrusSearch\Search\SearchContext;
use CirrusSearch\Util;
use CirrusSearch\WarningCollector;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use MediaWiki\Config\Config;
use Wikibase\Search\Elastic\Fields\StatementsField;
use Wikimedia\Assert\Assert;

/**
 * Handles the search keyword 'haslicense:'
 *
 * Allows the user to search for sets of licence statements in statement_keywords
 *
 * A mapping between search strings and sets of statements is passed in the constructor in this
 * format:
 * [
 *     'cc-by-sa' => [
 *         'P275=Q18199165', // copyright licence = CC-BY-SA 4.0
 *     ],
 *     'cc-by' => [
 *         'P275=Q19125117', // copyright licence = CC-BY 2.0
 *     ],
 *     'unrestricted' => [
 *         'P275=Q6938433', // copyright licence = cc0
 *         'P6216=Q19652', // copyright status = public domain
 *     ]
 * ]
 *
 * So searching for `haslicense:cc-by` searches for documents with P275=Q19125117 in
 * statement_keywords
 *
 * A search for `haslicense:other` will return pages that have ANY of the *properties*
 * from the licence mapping array AND NONE of the statements.
 *
 * A user can search for more than one type of licence by combining the search strings using the |
 * character. Note that combining "other" with other licence types will result in *only* "other"
 * licences being returned, because "other" specifically excludes all other licence types.
 *
 * So for the config above, searching for `haslicense:other` searches for documents with (P275 OR
 * P6216 in statement_keywords.property) AND NOT (P275=Q18199165 OR P275=Q19125117 OR P275=Q6938433
 * OR P6216=Q19652 in statement_keywords)
 *
 * @uses CirrusSearch
 * @see https://phabricator.wikimedia.org/T257938
 */
class HasLicenseFeature extends SimpleKeywordFeature implements FilterQueryFeature {

	/**
	 * @var array
	 */
	private $licenseMapping;

	/**
	 * @param array $licenseMapping Mapping between licence search strings and wikidata ids
	 * 	e.g. [
	 *     'cc-by-sa' => [
	 *         'P275=Q18199165', // copyright licence = CC-BY-SA 4.0
	 *     ],
	 *     'cc-by' => [
	 *         'P275=Q19125117', // copyright licence = CC-BY 2.0
	 *     ],
	 *     'unrestricted' => [
	 *         'P275=Q6938433', // copyright licence = cc0
	 *         'P6216=Q19652', // copyright status = public domain
	 *     ]
	 * ]
	 */
	public function __construct( $licenseMapping ) {
		Assert::parameterElementType( 'array', $licenseMapping, 'licenseMapping' );
		$this->licenseMapping = $licenseMapping;
	}

	/**
	 * @return string[]
	 */
	protected function getKeywords() {
		return [ 'haslicense' ];
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
	 *  string (always false for this class)
	 */
	protected function doApply( SearchContext $context, $key, $value, $quotedValue, $negated ) {
		if ( $value === '' ) {
			return [ null, false ];
		}
		$queries = $this->parseValue(
			$key,
			$value,
			$quotedValue,
			'',
			'',
			$context
		);
		if ( count( $queries ) == 0 ) {
			$context->setResultsPossible( false );
			return [ null, false ];
		}

		return [ $this->combineQueries( $queries ), false ];
	}

	/**
	 * @param string[][] $queries queries to combine. See parseValue() for fields.
	 * @return \Elastica\Query\AbstractQuery
	 */
	private function combineQueries( array $queries ) {
		$return = new BoolQuery();
		$return->setMinimumShouldMatch( 1 );
		foreach ( $queries as $query ) {
			if ( $query['occur'] === 'must_not' ) {
				$return->addMustNot( new MatchQuery(
					$query['field'],
					[ 'query' => $query['string'] ]
				) );
			} elseif ( $query['occur'] === 'should' ) {
				$return->addShould( new MatchQuery( $query['field'], [ 'query' => $query['string'] ] ) );
			}
		}
		return $return;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param string $quotedValue
	 * @param string $valueDelimiter
	 * @param string $suffix
	 * @param WarningCollector $warningCollector
	 * @return array [
	 *     [
	 *         'class' => \Elastica\Query class name to be used to construct the query,
	 *         'field' => document field to run the query against,
	 *         'string' => string to search for
	 *     ],
	 *     ...
	 * ]
	 */
	public function parseValue(
		$key,
		$value,
		$quotedValue,
		$valueDelimiter,
		$suffix,
		WarningCollector $warningCollector
	) {
		$queries = [];
		$licenseStrings = explode( '|', $value );
		$licenseStrings = array_slice( $licenseStrings, 0, 20 );
		foreach ( $licenseStrings as $licenseString ) {
			$queries = array_merge( $queries, $this->licenseStringToQueries( $licenseString ) );
		}
		if ( count( $queries ) === 0 ) {
			$warningCollector->addWarning(
				'wikibasecirrus-haslicense-feature-no-valid-arguments',
				$key
			);
		}
		return $queries;
	}

	private function licenseStringToQueries( $licenseString ) {
		$queries = [];
		if ( $licenseString === 'other' ) {
			return $this->getQueriesForOther();
		}
		if ( !isset( $this->licenseMapping[ $licenseString ] ) ) {
			return $queries;
		}
		foreach ( $this->licenseMapping[ $licenseString ] as $statementString ) {
			$queries[] = [
				'occur' => 'should',
				'field' => StatementsField::NAME,
				'string' => $statementString,
			];
		}
		return $queries;
	}

	/**
	 * For "other" licence types, search for results that match the properties
	 * but not the statements
	 */
	private function getQueriesForOther() {
		$queries = [];
		foreach ( $this->licenseMapping as $mapping ) {
			foreach ( $mapping as $statementString ) {
				[ $propertyId, ] = explode( '=', $statementString );
				if ( !isset( $queries[$propertyId] ) ) {
					$queries[$propertyId] = [
						'occur' => 'should',
						'field' => StatementsField::NAME . '.property',
						'string' => $propertyId,
					];
				}
				$queries[] = [
					'occur' => 'must_not',
					'field' => StatementsField::NAME,
					'string' => $statementString,
				];
			}
		}
		return array_values( $queries );
	}

	/**
	 * @param KeywordFeatureNode $node
	 * @param QueryBuildingContext $context
	 * @return AbstractQuery|null
	 */
	public function getFilterQuery( KeywordFeatureNode $node, QueryBuildingContext $context ) {
		$statements = $node->getParsedValue();
		if ( $statements === [] ) {
			return null;
		}
		return $this->combineQueries( $statements );
	}

	/**
	 * License mapping can come a message, allowing wiki-specific config/overrides,
	 * controlled by users, or in code config (which overrides messages)
	 *
	 * @param Config $searchConfig
	 * @return array
	 */
	public static function getConfiguredLicenseMap( Config $searchConfig ) {
		// license mapping can come a message, allowing wiki-specific config/overrides,
		// controlled by users, or in code config (which overrides messages)
		$licenseMapping = $searchConfig->get( 'LicenseMapping' ) ?: [];
		$licenseMessage = wfMessage( 'wikibasecirrus-license-mapping' )->inContentLanguage();
		if ( !$licenseMapping && !$licenseMessage->isDisabled() ) {
			$lines = Util::parseSettingsInMessage( $licenseMessage->plain() );
			// reformat lines to allow for whitespace in the license config
			$joined = implode( "\n", $lines );
			$stripped = preg_replace( '/\n*?([|,])\n?(?![^\n]+\|)/', '$1', $joined );
			$lines = explode( "\n", $stripped );
			// parse message, add to license mapping
			foreach ( $lines as $line ) {
				$data = explode( '|', $line );
				if ( count( $data ) === 2 ) {
					$licenseMapping[$data[0]] = array_filter( explode( ',', $data[1] ) );
				}
			}
		}
		return $licenseMapping;
	}
}
