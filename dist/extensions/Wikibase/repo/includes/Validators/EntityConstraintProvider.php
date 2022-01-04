<?php

namespace Wikibase\Repo\Validators;

use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\Property;
use Wikibase\Repo\Store\SiteLinkConflictLookup;

/**
 * Provides constraints for each entity type.
 * Used to enforce global constraints upon save.
 *
 * @see docs/constraints.wiki
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class EntityConstraintProvider {

	/**
	 * @var SiteLinkConflictLookup
	 */
	private $siteLinkConflictLookup;

	/**
	 * @var EntityValidator
	 */
	private $labelUniquenessValidator;

	public function __construct(
		SiteLinkConflictLookup $siteLinkConflictLookup,
		TermValidatorFactory $termValidatorFactory
	) {
		$this->siteLinkConflictLookup = $siteLinkConflictLookup;
		$this->labelUniquenessValidator = $termValidatorFactory->getLabelUniquenessValidator( Property::ENTITY_TYPE );

		//TODO: Make validators configurable. Allow more types to register.
	}

	/**
	 * Returns validators for hard global constraints that should be enforced on every update
	 * of an entity of the given type (including creation).
	 *
	 * @param string $entityType
	 *
	 * @return EntityValidator[]
	 */
	public function getUpdateValidators( $entityType ) {
		$validators = [];

		if ( $entityType === Item::ENTITY_TYPE ) {
			$validators[] = new SiteLinkUniquenessValidator( $this->siteLinkConflictLookup );
		}

		if ( $entityType === Property::ENTITY_TYPE ) {
			$validators[] = $this->labelUniquenessValidator;
		}

		return $validators;
	}

	/**
	 * Returns validators for soft global constraints that should be enforced only
	 * upon creation of an entity of the given type. This will include at least the
	 * validators returned by getUpdateValidators() for that type.
	 *
	 * @note During updates, such soft constraints should be checked selectively by the
	 * respective ChangeOps, so not all such (potentially expensive) validators are applied
	 * for all updates.
	 *
	 * @return EntityValidator[]
	 */
	public function getCreationValidators( $entityType, EntityId $entityId ): array {
		return $this->getUpdateValidators( $entityType );
	}

}
