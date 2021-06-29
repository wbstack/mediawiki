<?php
namespace Wikibase\Lexeme\Search\Elastic;

use CirrusSearch\CirrusSearch;
use CirrusSearch\Search\KeywordIndexField;
use SearchEngine;
use SearchIndexField;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lexeme\Domain\Model\Lexeme;
use Wikibase\Search\Elastic\Fields\TermIndexField;

/**
 * Field for Lexeme Forms
 */
class FormsField extends TermIndexField {

	public const NAME = 'lexeme_forms';

	public function __construct() {
		parent::__construct( static::NAME, \SearchIndexField::INDEX_TYPE_TEXT );
	}

	/**
	 * @param SearchEngine $engine
	 * @return array
	 */
	public function getMapping( SearchEngine $engine ) {
		// Since we need a specially tuned field, we can not use
		// standard search engine types.
		if ( !( $engine instanceof CirrusSearch ) ) {
			// For now only Cirrus/Elastic is supported
			return [];
		}

		$reprConfig = $this->getUnindexedField();

		$reprConfig['fields']['prefix'] =
			$this->getSubfield( 'prefix_asciifolding', 'near_match_asciifolding' );
		$reprConfig['fields']['near_match'] = $this->getSubfield( 'near_match' );
		$reprConfig['fields']['near_match_folded'] = $this->getSubfield( 'near_match_asciifolding' );
		// TODO: we don't seem to be using this, check if we need it?
		$reprConfig['copy_to'] = 'labels_all';

		$keyword = new KeywordIndexField( $this->getName(), SearchIndexField::INDEX_TYPE_KEYWORD,
				$engine->getConfig() );
		$keyword->setFlag( self::FLAG_CASEFOLD );
		$keywordMapping = $keyword->getMapping( $engine );

		$config = [
			'type' => 'object',
			'properties' => [
				'id' => $keywordMapping,
				'representation' => $reprConfig,
			]
		];

		return $config;
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @return mixed Get the value of the field to be indexed when a page/document
	 *               is indexed. This might be an array with nested data, if the field
	 *               is defined with nested type or an int or string for simple field types.
	 */
	public function getFieldData( EntityDocument $entity ) {
		if ( !( $entity instanceof Lexeme ) ) {
			return [];
		}
		/**
		 * @var Lexeme $entity
		 */
		$data = [];
		foreach ( $entity->getForms()->toArray() as $form ) {
			$data[] = [
				"id" => $form->getId()->getSerialization(),
				"representation" => array_values( $form->getRepresentations()->toTextArray() ),
				"features" => array_map( function ( ItemId $item ) {
									return $item->getSerialization();
				}, $form->getGrammaticalFeatures() )
			];
		}
		return $data;
	}

}
