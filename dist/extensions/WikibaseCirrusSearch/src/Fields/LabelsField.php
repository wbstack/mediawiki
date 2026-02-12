<?php
namespace Wikibase\Search\Elastic\Fields;

use CirrusSearch\CirrusSearch;
use SearchEngine;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Term\AliasesProvider;
use Wikibase\DataModel\Term\LabelsProvider;

/**
 * Field which contains per-language specific labels.
 *
 * @license GPL-2.0-or-later
 * @author Stas Malyshev
 */
class LabelsField extends TermIndexField {

	/**
	 * Field name
	 */
	public const NAME = "labels";

	/**
	 * List of available languages
	 * @var string[]
	 */
	private $languages;

	/**
	 * @var array
	 */
	private $stemmingSettings;

	/**
	 * @param string[] $languages
	 */
	public function __construct( array $languages, array $stemmingSettings ) {
		$this->languages = $languages;
		parent::__construct( self::NAME, \SearchIndexField::INDEX_TYPE_NESTED );
		$this->stemmingSettings = $stemmingSettings;
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

		$config = [
			'type' => 'object',
			'properties' => []
		];
		foreach ( $this->languages as $language ) {
			if ( empty( $this->stemmingSettings[$language]['index'] ) ) {
				$langConfig = $this->getUnindexedField();
			} else {
				$langConfig = $this->getTokenizedSubfield( $engine->getConfig(),
					$language . '_text',
					$language . '_text_search'
				);
			}

			$langConfig['fields']['prefix'] =
				$this->getSubfield( 'prefix_asciifolding', 'near_match_asciifolding' );
			$langConfig['fields']['near_match_folded'] =
				$this->getSubfield( 'near_match_asciifolding' );
			$langConfig['fields']['near_match'] = $this->getSubfield( 'near_match' );
			// This one is for full-text search, will tokenize
			// TODO: here we probably will need better language-specific analyzers
			$langConfig['fields']['plain'] = $this->getTokenizedSubfield( $engine->getConfig(),
				$language . '_plain', $language . '_plain_search' );
			// All labels are copies to labels_all
			$langConfig['copy_to'] = 'labels_all';

			$config['properties'][$language] = $langConfig;
		}

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
		if ( !( $entity instanceof LabelsProvider ) ) {
			return null;
		}
		$data = [];
		foreach ( $entity->getLabels() as $language => $label ) {
			$data[$language][] = $label->getText();
		}
		if ( $entity instanceof AliasesProvider ) {
			foreach ( $entity->getAliasGroups() as $aliases ) {
				$language = $aliases->getLanguageCode();
				if ( !isset( $data[$language] ) ) {
					$data[$language][] = '';
				}
				$data[$language] = array_merge( $data[$language], $aliases->getAliases() );
			}
		}
		// Shouldn't return empty arrays, that will be encoded to json as an
		// empty list instead of an empty map. Elastic doesn't mind, but this
		// allows more consistency working with the resulting cirrus docs
		return $data ?: null;
	}

	/**
	 * Set engine hints.
	 * Specifically, sets noop hint so that labels would be compared
	 * as arrays and removal of labels would be processed correctly.
	 * @param SearchEngine $engine
	 * @return array
	 */
	public function getEngineHints( SearchEngine $engine ) {
		if ( !( $engine instanceof CirrusSearch ) ) {
			// For now only Cirrus/Elastic is supported
			return [];
		}
		return [ \CirrusSearch\Search\CirrusIndexField::NOOP_HINT => "equals" ];
	}

}
