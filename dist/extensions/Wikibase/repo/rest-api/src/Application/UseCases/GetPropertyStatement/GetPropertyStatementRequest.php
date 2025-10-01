<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\GetPropertyStatement;

use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\PropertyStatementIdRequest;
use Wikibase\Repo\RestApi\Application\UseCases\GetStatement\GetStatementRequest;

/**
 * @license GPL-2.0-or-later
 */
class GetPropertyStatementRequest extends GetStatementRequest implements PropertyStatementIdRequest {

	private string $propertyId;

	public function __construct( string $propertyId, string $statementId ) {
		parent::__construct( $statementId );
		$this->propertyId = $propertyId;
	}

	public function getPropertyId(): string {
		return $this->propertyId;
	}
}
