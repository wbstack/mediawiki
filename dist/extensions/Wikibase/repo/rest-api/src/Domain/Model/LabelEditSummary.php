<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Domain\Model;

use Wikibase\DataModel\Term\Term;

/**
 * @license GPL-2.0-or-later
 */
class LabelEditSummary implements EditSummary {

	private string $editAction;
	private ?string $userComment;
	private Term $term;

	private function __construct( string $editAction, ?string $userComment, Term $term ) {
		$this->editAction = $editAction;
		$this->userComment = $userComment;
		$this->term = $term;
	}

	public static function newAddSummary( ?string $userComment, Term $term ): self {
		return new self( self::ADD_ACTION, $userComment, $term );
	}

	public static function newReplaceSummary( ?string $userComment, Term $term ): self {
		return new self( self::REPLACE_ACTION, $userComment, $term );
	}

	public static function newRemoveSummary( ?string $userComment, Term $label ): self {
		return new self( self::REMOVE_ACTION, $userComment, $label );
	}

	public function getEditAction(): string {
		return $this->editAction;
	}

	public function getUserComment(): ?string {
		return $this->userComment;
	}

	public function getLabel(): Term {
		return $this->term;
	}

}
