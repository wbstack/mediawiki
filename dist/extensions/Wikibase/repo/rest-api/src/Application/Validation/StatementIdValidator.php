<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\Validation;

use Wikibase\DataModel\Entity\EntityIdParser;
use Wikibase\DataModel\Services\Statement\StatementGuidValidator;

/**
 * @license GPL-2.0-or-later
 */
class StatementIdValidator {

	public const CODE_INVALID = 'statement-id-validator-code-invalid-statement-id';
	public const CONTEXT_VALUE = 'statement-id-validator-context-statement-id-value';

	private EntityIdParser $entityIdParser;

	public function __construct( EntityIdParser $entityIdParser ) {
		$this->entityIdParser = $entityIdParser;
	}

	public function validate( string $statementId ): ?ValidationError {
		$statementGuidValidator = new StatementGuidValidator( $this->entityIdParser );
		if ( !$statementGuidValidator->validate( $statementId ) ) {
			return new ValidationError( self::CODE_INVALID, [ self::CONTEXT_VALUE => $statementId ] );
		}

		return null;
	}
}
