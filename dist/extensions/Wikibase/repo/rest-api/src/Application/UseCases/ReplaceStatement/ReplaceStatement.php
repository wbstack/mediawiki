<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\ReplaceStatement;

use Wikibase\DataModel\Exception\PropertyChangedException;
use Wikibase\DataModel\Exception\StatementNotFoundException;
use Wikibase\Repo\RestApi\Application\UseCases\AssertStatementSubjectExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertUserIsAuthorized;
use Wikibase\Repo\RestApi\Application\UseCases\ItemRedirect;
use Wikibase\Repo\RestApi\Application\UseCases\UpdateExceptionHandler;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Domain\Model\EditMetadata;
use Wikibase\Repo\RestApi\Domain\Model\StatementEditSummary;
use Wikibase\Repo\RestApi\Domain\Services\StatementUpdater;

/**
 * @license GPL-2.0-or-later
 */
class ReplaceStatement {

	use UpdateExceptionHandler;

	private ReplaceStatementValidator $validator;
	private AssertStatementSubjectExists $assertStatementSubjectExists;
	private AssertUserIsAuthorized $assertUserIsAuthorized;
	private StatementUpdater $statementUpdater;

	public function __construct(
		ReplaceStatementValidator $validator,
		AssertStatementSubjectExists $assertStatementSubjectExists,
		AssertUserIsAuthorized $assertUserIsAuthorized,
		StatementUpdater $statementUpdater
	) {
		$this->validator = $validator;
		$this->assertStatementSubjectExists = $assertStatementSubjectExists;
		$this->assertUserIsAuthorized = $assertUserIsAuthorized;
		$this->statementUpdater = $statementUpdater;
	}

	/**
	 * @throws ItemRedirect
	 * @throws UseCaseError
	 */
	public function execute( ReplaceStatementRequest $request ): ReplaceStatementResponse {
		$deserializedRequest = $this->validator->validateAndDeserialize( $request );
		$statementId = $deserializedRequest->getStatementId();
		$statement = $deserializedRequest->getStatement();
		$editMetadata = $deserializedRequest->getEditMetadata();

		$this->assertStatementSubjectExists->execute( $statementId );
		$this->assertUserIsAuthorized->checkEditPermissions( $statementId->getEntityId(), $editMetadata->getUser() );

		if ( $statement->getGuid() === null ) {
			$statement->setGuid( "$statementId" );
		} elseif ( $statement->getGuid() !== "$statementId" ) {
			throw new UseCaseError(
				UseCaseError::CANNOT_MODIFY_READ_ONLY_VALUE,
				'The input value cannot be modified',
				[ UseCaseError::CONTEXT_PATH => '/statement/id' ]
			);
		}

		try {
			$newRevision = $this->executeWithExceptionHandling( fn() => $this->statementUpdater->update(
				$statement,
				new EditMetadata(
					$editMetadata->getTags(),
					$editMetadata->isBot(),
					StatementEditSummary::newReplaceSummary( $editMetadata->getComment(), $statement )
				)
			) );
		} catch ( StatementNotFoundException $e ) {
			throw UseCaseError::newResourceNotFound( 'statement' );
		} catch ( PropertyChangedException $e ) {
			throw new UseCaseError(
				UseCaseError::CANNOT_MODIFY_READ_ONLY_VALUE,
				'The input value cannot be modified',
				[ UseCaseError::CONTEXT_PATH => '/statement/property/id' ]
			);
		}

		return new ReplaceStatementResponse(
			$newRevision->getStatement(),
			$newRevision->getLastModified(),
			$newRevision->getRevisionId()
		);
	}

}
