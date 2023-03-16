<?php
namespace Wikibase\Search\Elastic;

use Elastica\Query\ConstantScore;
use Elastica\Query\MatchQuery;
use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Entity\EntityIdParsingException;
use Wikibase\DataModel\Term\Term;
use Wikibase\Lib\TermLanguageFallbackChain;

/**
 * Utilities useful for entity searches.
 */
final class EntitySearchUtils {

	/**
	 * Create constant score query for a field.
	 * @param string $field
	 * @param string|double $boost
	 * @param string $text
	 * @return ConstantScore
	 */
	public static function makeConstScoreQuery( $field, $boost, $text ) {
		$csquery = new ConstantScore();
		$csquery->setFilter( new MatchQuery( $field, $text ) );
		$csquery->setBoost( $boost );
		return $csquery;
	}

	/**
	 * If the text looks like ID, normalize it to ID title
	 * Cases handled:
	 * - q42
	 * - (q42)
	 * - leading/trailing spaces
	 * - http://www.wikidata.org/entity/Q42
	 * @param string $text
	 * @param EntityIdParser $idParser
	 * @return string Normalized ID or original string
	 */
	public static function normalizeId( $text, EntityIdParser $idParser ) {
		// TODO: this is a bit hacky, better way would be to make the field case-insensitive
		// or add new subfiled which is case-insensitive
		$text = strtoupper( str_replace( [ '(', ')' ], '', trim( $text ) ) );
		$id = self::parseOrNull( $text, $idParser );
		if ( $id ) {
			return $id->getSerialization();
		}
		if ( preg_match( '/\b(\w+)$/', $text, $matches ) && $matches[1] ) {
			$id = self::parseOrNull( $matches[1], $idParser );
			if ( $id ) {
				return $id->getSerialization();
			}
		}
		return $text;
	}

	/**
	 * Parse entity ID or return null
	 * @param string $text
	 * @param EntityIdParser $idParser
	 * @return null|\Wikibase\DataModel\Entity\EntityId
	 */
	public static function parseOrNull( $text, EntityIdParser $idParser ) {
		try {
			$id = $idParser->parse( $text );
		} catch ( EntityIdParsingException $ex ) {
			return null;
		}
		return $id;
	}

	/**
	 * Locate label for display among the source data, basing on fallback chain.
	 * @param array $sourceData
	 * @param string $field
	 * @param TermLanguageFallbackChain $termFallbackChain
	 * @return null|Term
	 */
	public static function findTermForDisplay( $sourceData, $field, TermLanguageFallbackChain $termFallbackChain ) {
		if ( empty( $sourceData[$field] ) ) {
			return null;
		}

		$data = $sourceData[$field];
		$first = reset( $data );
		if ( is_array( $first ) ) {
			// If we have multiple, like for labels, extract the first one
			$labels_data = array_map(
				static function ( $data ) {
					return $data[0] ?? null;
				},
				$data
			);
		} else {
			$labels_data = $data;
		}
		// Drop empty ones
		$labels_data = array_filter( $labels_data );

		$preferredValue = $termFallbackChain->extractPreferredValueOrAny( $labels_data );
		if ( $preferredValue ) {
			return new Term( $preferredValue['language'], $preferredValue['value'] );
		}

		return null;
	}

}
