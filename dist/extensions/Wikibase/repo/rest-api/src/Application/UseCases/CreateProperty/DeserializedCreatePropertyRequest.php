<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\CreateProperty;

use Wikibase\DataModel\Entity\Property;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\DeserializedEditMetadataRequest;
use Wikibase\Repo\RestApi\Domain\Model\UserProvidedEditMetadata;

/**
 * @license GPL-2.0-or-later
 */
class DeserializedCreatePropertyRequest implements DeserializedEditMetadataRequest {

	private Property $property;
	private UserProvidedEditMetadata $editMetadata;

	public function __construct( Property $property, UserProvidedEditMetadata $editMetadata ) {
		$this->property = $property;
		$this->editMetadata = $editMetadata;
	}

	public function getProperty(): Property {
		return $this->property;
	}

	public function getEditMetadata(): UserProvidedEditMetadata {
		return $this->editMetadata;
	}
}
