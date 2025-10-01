<?php declare( strict_types = 1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\GetItemLabelWithFallback;

use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;

/**
 * @license GPL-2.0-or-later
 */
interface GetItemLabelWithFallbackValidator {

	/**
	 * @throws UseCaseError
	 */
	public function validateAndDeserialize( GetItemLabelWithFallbackRequest $request ): DeserializedGetItemLabelWithFallbackRequest;

}
