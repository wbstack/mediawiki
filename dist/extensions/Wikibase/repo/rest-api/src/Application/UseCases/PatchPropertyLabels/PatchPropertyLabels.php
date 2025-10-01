<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\PatchPropertyLabels;

use Wikibase\Repo\RestApi\Application\Serialization\LabelsSerializer;
use Wikibase\Repo\RestApi\Application\UseCases\AssertPropertyExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertUserIsAuthorized;
use Wikibase\Repo\RestApi\Application\UseCases\PatchJson;
use Wikibase\Repo\RestApi\Application\UseCases\UpdateExceptionHandler;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Domain\Model\EditMetadata;
use Wikibase\Repo\RestApi\Domain\Model\LabelsEditSummary;
use Wikibase\Repo\RestApi\Domain\Services\PropertyLabelsRetriever;
use Wikibase\Repo\RestApi\Domain\Services\PropertyUpdater;
use Wikibase\Repo\RestApi\Domain\Services\PropertyWriteModelRetriever;

/**
 * @license GPL-2.0-or-later
 */
class PatchPropertyLabels {

	use UpdateExceptionHandler;

	private PropertyLabelsRetriever $labelsRetriever;
	private LabelsSerializer $labelsSerializer;
	private PatchJson $patcher;
	private PropertyWriteModelRetriever $propertyRetriever;
	private PropertyUpdater $propertyUpdater;
	private PatchPropertyLabelsValidator $useCaseValidator;
	private PatchedPropertyLabelsValidator $patchedLabelsValidator;
	private AssertPropertyExists $assertPropertyExists;
	private AssertUserIsAuthorized $assertUserIsAuthorized;

	public function __construct(
		PropertyLabelsRetriever $labelsRetriever,
		LabelsSerializer $labelsSerializer,
		PatchJson $patcher,
		PropertyWriteModelRetriever $propertyRetriever,
		PropertyUpdater $propertyUpdater,
		PatchPropertyLabelsValidator $useCaseValidator,
		PatchedPropertyLabelsValidator $patchedLabelsValidator,
		AssertPropertyExists $assertPropertyExists,
		AssertUserIsAuthorized $assertUserIsAuthorized
	) {
		$this->labelsRetriever = $labelsRetriever;
		$this->labelsSerializer = $labelsSerializer;
		$this->patcher = $patcher;
		$this->propertyRetriever = $propertyRetriever;
		$this->propertyUpdater = $propertyUpdater;
		$this->useCaseValidator = $useCaseValidator;
		$this->patchedLabelsValidator = $patchedLabelsValidator;
		$this->assertPropertyExists = $assertPropertyExists;
		$this->assertUserIsAuthorized = $assertUserIsAuthorized;
	}

	/**
	 * @throws UseCaseError
	 */
	public function execute( PatchPropertyLabelsRequest $request ): PatchPropertyLabelsResponse {
		$deserializedRequest = $this->useCaseValidator->validateAndDeserialize( $request );
		$propertyId = $deserializedRequest->getPropertyId();

		$this->assertPropertyExists->execute( $propertyId );

		$this->assertUserIsAuthorized->checkEditPermissions(
			$deserializedRequest->getPropertyId(),
			$deserializedRequest->getEditMetadata()->getUser()
		);

		$modifiedLabels = $this->patcher->execute(
			iterator_to_array(
				// @phan-suppress-next-line PhanTypeMismatchArgumentNullable
				$this->labelsSerializer->serialize( $this->labelsRetriever->getLabels( $propertyId ) )
			),
			$deserializedRequest->getPatch()
		);

		$property = $this->propertyRetriever->getPropertyWriteModel( $propertyId );
		$originalLabels = $property->getLabels();

		$modifiedLabelsAsTermList = $this->patchedLabelsValidator->validateAndDeserialize(
			$originalLabels,
			$property->getDescriptions(),
			$modifiedLabels
		);
		$property->getFingerprint()->setLabels( $modifiedLabelsAsTermList );

		$editMetadata = new EditMetadata(
			$deserializedRequest->getEditMetadata()->getTags(),
			$deserializedRequest->getEditMetadata()->isBot(),
			LabelsEditSummary::newPatchSummary(
				$deserializedRequest->getEditMetadata()->getComment(),
				$originalLabels,
				$modifiedLabelsAsTermList
			)
		);

		$revision = $this->executeWithExceptionHandling( fn() => $this->propertyUpdater->update(
			$property, // @phan-suppress-current-line PhanTypeMismatchArgumentNullable
			$editMetadata
		) );

		return new PatchPropertyLabelsResponse(
			$revision->getProperty()->getLabels(),
			$revision->getLastModified(),
			$revision->getRevisionId()
		);
	}

}
