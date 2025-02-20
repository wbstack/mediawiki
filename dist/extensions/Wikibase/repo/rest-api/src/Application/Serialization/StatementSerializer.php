<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\Serialization;

use Wikibase\DataModel\Statement\Statement as StatementWriteModel;
use Wikibase\Repo\RestApi\Domain\ReadModel\PropertyValuePair;
use Wikibase\Repo\RestApi\Domain\ReadModel\Reference;
use Wikibase\Repo\RestApi\Domain\ReadModel\Statement;

/**
 * @license GPL-2.0-or-later
 */
class StatementSerializer {

	public const RANK_LABELS = [
		StatementWriteModel::RANK_DEPRECATED => 'deprecated',
		StatementWriteModel::RANK_NORMAL => 'normal',
		StatementWriteModel::RANK_PREFERRED => 'preferred',
	];
	private PropertyValuePairSerializer $propertyValuePairSerializer;
	private ReferenceSerializer $referenceSerializer;

	public function __construct( PropertyValuePairSerializer $propertyValuePairSerializer, ReferenceSerializer $referenceSerializer ) {
		$this->propertyValuePairSerializer = $propertyValuePairSerializer;
		$this->referenceSerializer = $referenceSerializer;
	}

	public function serialize( Statement $statement ): array {
		return array_merge(
			[
				'id' => (string)$statement->getGuid(),
				'rank' => self::RANK_LABELS[ $statement->getRank()->asInt() ],
				'qualifiers' => array_map(
					fn( PropertyValuePair $qualifier ) => $this->propertyValuePairSerializer->serialize( $qualifier ),
					iterator_to_array( $statement->getQualifiers() )
				),
				'references' => array_map(
					fn( Reference $reference ) => $this->referenceSerializer->serialize( $reference ),
					iterator_to_array( $statement->getReferences() )
				),
			],
			$this->propertyValuePairSerializer->serialize( new PropertyValuePair( $statement->getProperty(), $statement->getValue() ) )
		);
	}

}
