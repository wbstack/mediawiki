<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Domain\Model;

use Wikibase\DataModel\Entity\Item;

/**
 * @license GPL-2.0-or-later
 */
class ItemRevision {

	private $item;
	/**
	 * @var string timestamp in MediaWiki format 'YYYYMMDDhhmmss'
	 */
	private $lastModified;
	private $revisionId;

	public function __construct( Item $item, string $lastModified, int $revisionId ) {
		$this->item = $item;
		$this->lastModified = $lastModified;
		$this->revisionId = $revisionId;
	}

	public function getItem(): Item {
		return $this->item;
	}

	public function getLastModified(): string {
		return $this->lastModified;
	}

	public function getRevisionId(): int {
		return $this->revisionId;
	}
}
