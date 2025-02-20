<?php declare( strict_types = 1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\GetItemAliases;

use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;

/**
 * @license GPL-2.0-or-later
 */
interface GetItemAliasesValidator {

	/**
	 * @throws UseCaseError
	 */
	public function validateAndDeserialize( GetItemAliasesRequest $request ): DeserializedGetItemAliasesRequest;

}
