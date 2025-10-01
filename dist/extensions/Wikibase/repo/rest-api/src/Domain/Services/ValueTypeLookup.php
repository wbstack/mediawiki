<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Domain\Services;

/**
 * @license GPL-2.0-or-later
 */
interface ValueTypeLookup {

	public function getValueType( string $dataTypeId ): string;

}
