<?php

namespace Wikibase\Search\Elastic\Fields;

use Psr\Log\LoggerInterface;
use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\Lib\SettingsArray;
use Wikibase\Repo\Search\Fields\FieldDefinitions;
use Wikibase\Repo\Search\Fields\WikibaseIndexField;

/**
 * Fields for an object that has statements.
 *
 * @license GPL-2.0-or-later
 * @author Stas Malyshev
 */
class StatementProviderFieldDefinitions implements FieldDefinitions {

	/**
	 * List of properties to index.
	 * @var string[]
	 */
	private $propertyIds;

	/**
	 * @var callable[]
	 */
	private $searchIndexDataFormatters;
	/**
	 * @var PropertyDataTypeLookup
	 */
	private $propertyDataTypeLookup;
	/**
	 * @var array
	 */
	private $indexedTypes;
	/**
	 * @var array
	 */
	private $excludedIds;
	/**
	 * @var array
	 */
	private $allowedQualifierPropertyIdsForQuantityStatements;

	private ?LoggerInterface $logger;

	public function __construct(
		PropertyDataTypeLookup $propertyDataTypeLookup,
		array $searchIndexDataFormatters,
		array $propertyIds,
		array $indexedTypes,
		array $excludedIds,
		array $allowedQualifierPropertyIdsForQuantityStatements,
		?LoggerInterface $logger = null
	) {
		$this->propertyIds = $propertyIds;
		$this->searchIndexDataFormatters = $searchIndexDataFormatters;
		$this->propertyDataTypeLookup = $propertyDataTypeLookup;
		$this->indexedTypes = $indexedTypes;
		$this->excludedIds = $excludedIds;
		$this->allowedQualifierPropertyIdsForQuantityStatements =
			$allowedQualifierPropertyIdsForQuantityStatements;
		$this->logger = $logger;
	}

	/**
	 * Get the list of definitions
	 * @return WikibaseIndexField[] key is field name, value is WikibaseIndexField
	 */
	public function getFields() {
		$fields = [
			StatementsField::NAME => new StatementsField(
				$this->propertyDataTypeLookup,
				$this->propertyIds,
				$this->indexedTypes,
				$this->excludedIds,
				$this->searchIndexDataFormatters,
				$this->logger
			),
			StatementCountField::NAME => new StatementCountField(),
		];
		if ( $this->allowedQualifierPropertyIdsForQuantityStatements ) {
			$fields[StatementQuantityField::NAME] = new StatementQuantityField(
				$this->propertyDataTypeLookup,
				$this->propertyIds,
				$this->indexedTypes,
				$this->excludedIds,
				$this->searchIndexDataFormatters,
				$this->allowedQualifierPropertyIdsForQuantityStatements,
				$this->logger
			);
		}
		return $fields;
	}

	/**
	 * Factory to create StatementProviderFieldDefinitions from configs
	 * @param PropertyDataTypeLookup $propertyDataTypeLookup
	 * @param callable[] $searchIndexDataFormatters
	 * @param SettingsArray $settings
	 * @param LoggerInterface|null $logger
	 * @return StatementProviderFieldDefinitions
	 */
	public static function newFromSettings(
		PropertyDataTypeLookup $propertyDataTypeLookup,
		array $searchIndexDataFormatters,
		SettingsArray $settings,
		?LoggerInterface $logger = null
	) {
		return new static( $propertyDataTypeLookup, $searchIndexDataFormatters,
			$settings->getSetting( 'searchIndexProperties' ),
			$settings->getSetting( 'searchIndexTypes' ),
			$settings->getSetting( 'searchIndexPropertiesExclude' ),
			$settings->getSetting( 'searchIndexQualifierPropertiesForQuantity' ),
			$logger
		);
	}

}
