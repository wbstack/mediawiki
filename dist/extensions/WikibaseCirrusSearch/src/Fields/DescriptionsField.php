<?php

namespace Wikibase\Search\Elastic\Fields;

use CirrusSearch\CirrusSearch;
use SearchEngine;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Term\DescriptionsProvider;

/**
 * Field which contains per-language specific descriptions.
 *
 * @license GPL-2.0-or-later
 * @author Stas Malyshev
 */
class DescriptionsField extends TermIndexField {

	/**
	 * Field name
	 */
	public const NAME = 'descriptions';

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
	 * @param string[] $languages Available languages list.
	 * @param array $stemmingSettings Stemming config
	 */
	public function __construct( array $languages, array $stemmingSettings ) {
		$this->languages = $languages;
		parent::__construct( static::NAME, \SearchIndexField::INDEX_TYPE_NESTED );
		$this->stemmingSettings = $stemmingSettings;
	}

	/**
	 * @param SearchEngine $engine
	 * @return null|array
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
			// TODO: here we probably will need better language-specific analyzers
			if ( empty( $this->stemmingSettings[$language]['index'] ) ) {
				$langConfig = $this->getUnindexedField();
			} else {
				$langConfig = $this->getTokenizedSubfield( $engine->getConfig(),
					$language . '_text',
					$language . '_text_search'
				);
			}
			$langConfig['fields']['plain'] = $this->getTokenizedSubfield( $engine->getConfig(), $language . '_plain',
					$language . '_plain_search' );
			$config['properties'][$language] = $langConfig;
		}

		return $config;
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @return array|null Array of descriptions in available languages.
	 */
	public function getFieldData( EntityDocument $entity ) {
		if ( !( $entity instanceof DescriptionsProvider ) ) {
			return null;
		}
		$data = [];
		foreach ( $entity->getDescriptions() as $language => $desc ) {
			// While wikibase can only have a single description,
			// WikibaseMediaInfo reports an array of descriptions. To keep the
			// constructed search docs consistent report an array here as well.
			$data[$language] = [ $desc->getText() ];
		}
		// Shouldn't return empty arrays, that will be encoded to json as an
		// empty list instead of an empty map. Elastic doesn't mind, but this
		// allows more consistency working with the resulting cirrus docs
		return $data ?: null;
	}

	/**
	 * Set engine hints.
	 * Specifically, sets noop hint so that descriptions would be compared
	 * as arrays and removal of description would be processed correctly.
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
