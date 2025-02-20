<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Domain\ReadModel;

use Wikibase\DataModel\Entity\PropertyId;

/**
 * @license GPL-2.0-or-later
 */
class Property {

	private PropertyId $id;
	private string $dataType;
	private Labels $labels;
	private Descriptions $descriptions;
	private Aliases $aliases;
	private StatementList $statements;

	public function __construct(
		PropertyId $id,
		string $dataType,
		Labels $labels,
		Descriptions $descriptions,
		Aliases $aliases,
		StatementList $statements
	) {
		$this->id = $id;
		$this->dataType = $dataType;
		$this->labels = $labels;
		$this->descriptions = $descriptions;
		$this->aliases = $aliases;
		$this->statements = $statements;
	}

	public function getId(): PropertyId {
		return $this->id;
	}

	public function getDataType(): string {
		return $this->dataType;
	}

	public function getLabels(): Labels {
		return $this->labels;
	}

	public function getDescriptions(): Descriptions {
		return $this->descriptions;
	}

	public function getAliases(): Aliases {
		return $this->aliases;
	}

	public function getStatements(): StatementList {
		return $this->statements;
	}

}
