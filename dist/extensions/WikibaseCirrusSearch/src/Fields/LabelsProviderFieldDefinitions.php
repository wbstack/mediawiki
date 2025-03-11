<?php

namespace Wikibase\Search\Elastic\Fields;

use MediaWiki\Config\ConfigFactory;
use Wikibase\Repo\Search\Fields\FieldDefinitions;
use Wikibase\Repo\Search\Fields\WikibaseIndexField;

/**
 * Definitions for any entity that has labels.
 *
 * @license GPL-2.0-or-later
 * @author Stas Malyshev
 */
class LabelsProviderFieldDefinitions implements FieldDefinitions {

	/**
	 * @var string[]
	 */
	private $languageCodes;

	/**
	 * @var array
	 */
	private $stemmingSettings;

	/**
	 * @param string[] $languageCodes
	 * @param ConfigFactory|null $configFactory
	 */
	public function __construct( array $languageCodes, ?ConfigFactory $configFactory = null ) {
		$this->languageCodes = $languageCodes;
		if ( $configFactory === null ) {
			$this->stemmingSettings = [];
		} else {
			$this->stemmingSettings = $configFactory->makeConfig( 'WikibaseCirrusSearch' )
				->get( 'UseStemming' );
		}
	}

	/**
	 * @return WikibaseIndexField[]
	 */
	public function getFields() {
		return [
			LabelsField::NAME => new LabelsField( $this->languageCodes, $this->stemmingSettings ),
			AllLabelsField::NAME => new AllLabelsField(),
		];
	}

}
