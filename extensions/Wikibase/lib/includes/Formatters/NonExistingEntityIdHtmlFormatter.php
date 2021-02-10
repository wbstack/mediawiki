<?php

namespace Wikibase\Lib\Formatters;

use Html;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Services\EntityId\EntityIdFormatter;

/**
 * Formats entity IDs by generating HTML for when the entity ID does not exist.
 *
 * @license GPL-2.0-or-later
 */
class NonExistingEntityIdHtmlFormatter implements EntityIdFormatter {

	private $deletedEntityMessagePrefix;

	/**
	 * @param string $deletedEntityMessagePrefix E.g. 'wikibase-deletedentity-'
	 */
	public function __construct( $deletedEntityMessagePrefix ) {
		$this->deletedEntityMessagePrefix = $deletedEntityMessagePrefix;
	}

	/**
	 * @see EntityIdFormatter::formatEntityId
	 *
	 * @param EntityId $entityId
	 *
	 * @return string HTML
	 */
	public function formatEntityId( EntityId $entityId ) {
		return $entityId->getSerialization() . $this->getUndefinedInfoMessage( $entityId );
	}

	/**
	 * @param EntityId $entityId
	 * @return string
	 */
	protected function getUndefinedInfoMessage( EntityId $entityId ) {
		$attributes = [ 'class' => 'wb-entity-undefinedinfo' ];

		$message = wfMessage( 'parentheses',
			wfMessage( $this->deletedEntityMessagePrefix . $entityId->getEntityType() )->text()
		)->text();

		$undefinedInfo = Html::element( 'span', $attributes, $message );

		$separator = wfMessage( 'word-separator' )->escaped();

		return $separator . $undefinedInfo;
	}
}
