<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\UseCases;

/**
 * @license GPL-2.0-or-later
 */
class ErrorResponse {
	public const COMMENT_TOO_LONG = 'comment-too-long';
	public const INVALID_EDIT_TAG = 'invalid-edit-tag';
	public const INVALID_FIELD = 'invalid-field';
	public const INVALID_ITEM_ID = 'invalid-item-id';
	public const INVALID_STATEMENT_ID = 'invalid-statement-id';
	public const INVALID_STATEMENT_DATA = 'invalid-statement-data';
	public const INVALID_OPERATION_CHANGED_STATEMENT_ID = 'invalid-operation-change-statement-id';
	public const INVALID_OPERATION_CHANGED_PROPERTY = 'invalid-operation-change-property-of-statement';
	public const INVALID_PATCH = 'invalid-patch';
	public const ITEM_NOT_FOUND = 'item-not-found';
	public const ITEM_REDIRECTED = 'redirected-item';
	public const PERMISSION_DENIED = 'permission-denied';
	public const STATEMENT_NOT_FOUND = 'statement-not-found';
	public const UNEXPECTED_ERROR = 'unexpected-error';

	private $code;
	private $message;

	public function __construct( string $code, string $message ) {
		$this->code = $code;
		$this->message = $message;
	}

	public function getCode(): string {
		return $this->code;
	}

	public function getMessage(): string {
		return $this->message;
	}
}
