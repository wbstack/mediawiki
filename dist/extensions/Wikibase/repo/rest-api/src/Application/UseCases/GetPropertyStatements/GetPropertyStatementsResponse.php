<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\GetPropertyStatements;

use Wikibase\Repo\RestApi\Domain\ReadModel\StatementList;

/**
 * @license GPL-2.0-or-later
 */
class GetPropertyStatementsResponse {

	private StatementList $statements;

	/**
	 * @var string timestamp in MediaWiki format 'YYYYMMDDhhmmss'
	 */
	private string $lastModified;

	private int $revisionId;

	public function __construct( StatementList $statements, string $lastModified, int $revisionId ) {
		$this->statements = $statements;
		$this->lastModified = $lastModified;
		$this->revisionId = $revisionId;
	}

	public function getStatements(): StatementList {
		return $this->statements;
	}

	public function getLastModified(): string {
		return $this->lastModified;
	}

	public function getRevisionId(): int {
		return $this->revisionId;
	}

}
