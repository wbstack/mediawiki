<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Domain\Services;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\RestApi\Domain\Model\ItemRevision;

/**
 * @license GPL-2.0-or-later
 */
interface ItemRevisionRetriever {

	public function getItemRevision( ItemId $itemId ): ?ItemRevision;
}
