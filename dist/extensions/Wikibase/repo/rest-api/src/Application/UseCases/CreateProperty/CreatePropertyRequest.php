<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCases\CreateProperty;

use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\EditMetadataRequest;

/**
 * @license GPL-2.0-or-later
 */
class CreatePropertyRequest implements EditMetadataRequest {

	private array $property;
	private array $editTags;
	private bool $isBot;
	private ?string $comment;
	private ?string $username;

	public function __construct( array $property, array $editTags, bool $isBot, ?string $comment, ?string $username ) {
		$this->property = $property;
		$this->editTags = $editTags;
		$this->isBot = $isBot;
		$this->comment = $comment;
		$this->username = $username;
	}

	public function getProperty(): array {
		return $this->property;
	}

	public function getEditTags(): array {
		return $this->editTags;
	}

	public function isBot(): bool {
		return $this->isBot;
	}

	public function getComment(): ?string {
		return $this->comment;
	}

	public function getUsername(): ?string {
		return $this->username;
	}
}
