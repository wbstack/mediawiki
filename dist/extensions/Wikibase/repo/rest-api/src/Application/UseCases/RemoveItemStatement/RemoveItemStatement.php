<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\RemoveItemStatement;

use Wikibase\Repo\RestApi\Application\UseCases\AssertItemExists;
use Wikibase\Repo\RestApi\Application\UseCases\ItemRedirect;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveStatement\RemoveStatement;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;

/**
 * @license GPL-2.0-or-later
 */
class RemoveItemStatement {

	private AssertItemExists $assertItemExists;
	private RemoveStatement $removeStatement;
	private RemoveItemStatementValidator $validator;

	public function __construct(
		AssertItemExists $assertItemExists,
		RemoveStatement $removeStatement,
		RemoveItemStatementValidator $validator
	) {
		$this->assertItemExists = $assertItemExists;
		$this->removeStatement = $removeStatement;
		$this->validator = $validator;
	}

	/**
	 * @throws ItemRedirect
	 * @throws UseCaseError
	 */
	public function execute( RemoveItemStatementRequest $request ): void {
		$deserializedRequest = $this->validator->validateAndDeserialize( $request );

		$this->assertItemExists->execute( $deserializedRequest->getItemId() );

		$this->removeStatement->execute( $request );
	}

}
