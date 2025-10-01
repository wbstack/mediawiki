<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\AddItemAliasesInLanguage;

use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\Repo\RestApi\Application\UseCases\AssertItemExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertUserIsAuthorized;
use Wikibase\Repo\RestApi\Application\UseCases\ItemRedirect;
use Wikibase\Repo\RestApi\Application\UseCases\UpdateExceptionHandler;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Domain\Model\AliasesInLanguageEditSummary;
use Wikibase\Repo\RestApi\Domain\Model\EditMetadata;
use Wikibase\Repo\RestApi\Domain\Services\ItemUpdater;
use Wikibase\Repo\RestApi\Domain\Services\ItemWriteModelRetriever;

/**
 * @license GPL-2.0-or-later
 */
class AddItemAliasesInLanguage {

	use UpdateExceptionHandler;

	private ItemWriteModelRetriever $itemRetriever;
	private AssertItemExists $assertItemExists;
	private AssertUserIsAuthorized $assertUserIsAuthorized;
	private ItemUpdater $itemUpdater;
	private AddItemAliasesInLanguageValidator $validator;

	public function __construct(
		ItemWriteModelRetriever $itemRetriever,
		AssertItemExists $assertItemExists,
		AssertUserIsAuthorized $assertUserIsAuthorized,
		ItemUpdater $itemUpdater,
		AddItemAliasesInLanguageValidator $validator
	) {
		$this->itemRetriever = $itemRetriever;
		$this->assertItemExists = $assertItemExists;
		$this->assertUserIsAuthorized = $assertUserIsAuthorized;
		$this->itemUpdater = $itemUpdater;
		$this->validator = $validator;
	}

	/**
	 * @throws UseCaseError
	 * @throws ItemRedirect
	 */
	public function execute( AddItemAliasesInLanguageRequest $request ): AddItemAliasesInLanguageResponse {
		$deserializedRequest = $this->validator->validateAndDeserialize( $request );

		$itemId = $deserializedRequest->getItemId();
		$languageCode = $deserializedRequest->getLanguageCode();
		$newAliases = $deserializedRequest->getItemAliasesInLanguage();
		$editMetadata = $deserializedRequest->getEditMetadata();

		$this->assertItemExists->execute( $itemId );
		$this->assertUserIsAuthorized->checkEditPermissions( $itemId, $editMetadata->getUser() );

		$item = $this->itemRetriever->getItemWriteModel( $itemId );
		$aliasesExist = $item->getAliasGroups()->hasGroupForLanguage( $languageCode );
		$originalAliases = $aliasesExist ? $item->getAliasGroups()->getByLanguage( $languageCode )->getAliases() : [];

		$item->getAliasGroups()->setAliasesForLanguage( $languageCode, array_unique( array_merge( $originalAliases, $newAliases ) ) );
		$newRevision = $this->executeWithExceptionHandling( fn() => $this->itemUpdater->update(
			$item, // @phan-suppress-current-line PhanTypeMismatchArgumentNullable
			new EditMetadata(
				$editMetadata->getTags(),
				$editMetadata->isBot(),
				AliasesInLanguageEditSummary::newAddSummary(
					$editMetadata->getComment(),
					new AliasGroup( $languageCode, array_diff( $newAliases, $originalAliases ) )
				)
			)
		) );

		return new AddItemAliasesInLanguageResponse(
			$newRevision->getItem()->getAliases()[ $languageCode ],
			$newRevision->getLastModified(),
			$newRevision->getRevisionId(),
			$aliasesExist
		);
	}

}
