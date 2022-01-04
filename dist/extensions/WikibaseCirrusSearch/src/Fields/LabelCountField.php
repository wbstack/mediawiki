<?php

namespace Wikibase\Search\Elastic\Fields;

use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Term\LabelsProvider;
use Wikibase\Repo\Search\Fields\WikibaseNumericField;

/**
 * @license GPL-2.0-or-later
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class LabelCountField extends WikibaseNumericField {

	/**
	 * Field name
	 */
	public const NAME = 'label_count';

	/**
	 * @see SearchIndexField::getFieldData
	 *
	 * @param EntityDocument $entity
	 *
	 * @return int
	 */
	public function getFieldData( EntityDocument $entity ) {
		if ( $entity instanceof LabelsProvider ) {
			return $entity->getLabels()->count();
		}

		return 0;
	}

}
