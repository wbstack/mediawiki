<?php declare( strict_types = 1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\GetItemAliasesInLanguage;

use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\AliasLanguageCodeRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemIdRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\UseCaseRequest;

/**
 * @license GPL-2.0-or-later
 */
class GetItemAliasesInLanguageRequest implements UseCaseRequest, ItemIdRequest, AliasLanguageCodeRequest {

	private string $itemId;
	private string $languageCode;

	public function __construct( string $itemId, string $languageCode ) {
		$this->itemId = $itemId;
		$this->languageCode = $languageCode;
	}

	public function getItemId(): string {
		return $this->itemId;
	}

	public function getLanguageCode(): string {
		return $this->languageCode;
	}

}
