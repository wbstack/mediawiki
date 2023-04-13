<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\UseCases\PatchItemStatement;

use CommentStore;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\ItemIdParser;
use Wikibase\DataModel\Statement\StatementGuid;
use Wikibase\Repo\RestApi\DataAccess\SnakValidatorStatementValidator;
use Wikibase\Repo\RestApi\Domain\Services\JsonPatchValidator;
use Wikibase\Repo\RestApi\UseCases\PatchItemStatement\PatchItemStatementRequest;
use Wikibase\Repo\RestApi\UseCases\PatchItemStatement\PatchItemStatementValidator;
use Wikibase\Repo\RestApi\Validation\EditMetadataValidator;
use Wikibase\Repo\RestApi\Validation\ItemIdValidator;
use Wikibase\Repo\RestApi\Validation\StatementIdValidator;
use Wikibase\Repo\RestApi\Validation\ValidationError;

/**
 * @covers \Wikibase\Repo\RestApi\UseCases\PatchItemStatement\PatchItemStatementValidator
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class PatchItemStatementValidatorTest extends TestCase {

	/**
	 * @var MockObject|SnakValidatorStatementValidator
	 */
	private $jsonPatchValidator;

	private const ALLOWED_TAGS = [ 'some', 'tags', 'are', 'allowed' ];

	protected function setUp(): void {
		parent::setUp();

		$this->jsonPatchValidator = $this->createStub( JsonPatchValidator::class );
		$this->jsonPatchValidator->method( 'validate' )->willReturn( null );
	}

	/**
	 * @dataProvider provideValidRequest
	 */
	public function testValidate_withValidRequest( array $requestData ): void {
		$error = $this->newPatchItemStatementValidator()->validate(
			$this->newUseCaseRequest( $requestData )
		);

		$this->assertNull( $error );
	}

	public function provideValidRequest(): Generator {
		$itemId = 'Q123';
		$statementId = $itemId . StatementGuid::SEPARATOR . 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
		yield 'Valid with item ID' => [
			[
				'$statementId' => $statementId,
				'$patch' => [ 'valid' => 'patch' ],
				'$editTags' => [],
				'$isBot' => false,
				'$comment' => null,
				'$username' => null,
				'$itemId' => $itemId
			]
		];
		yield 'Valid without item ID' => [
			[
				'$statementId' => $statementId,
				'$patch' => [ 'valid' => 'patch' ],
				'$editTags' => [],
				'$isBot' => false,
				'$comment' => null,
				'$username' => null
			]
		];
	}

	public function testValidate_withInvalidItemId(): void {
		$itemId = 'X123';
		$error = $this->newPatchItemStatementValidator()->validate(
			$this->newUseCaseRequest( [
				'$statementId' => 'Q123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE',
				'$patch' => [ 'valid' => 'patch' ],
				'$editTags' => [],
				'$isBot' => false,
				'$comment' => null,
				'$username' => null,
				'$itemId' => $itemId
			] )
		);

		$this->assertNotNull( $error );
		$this->assertSame( PatchItemStatementValidator::SOURCE_ITEM_ID, $error->getSource() );
		$this->assertSame( $itemId, $error->getValue() );
	}

	public function testValidate_withInvalidStatementId(): void {
		$itemId = 'Q123';
		$statementId = $itemId . StatementGuid::SEPARATOR . "INVALID-STATEMENT-ID";
		$error = $this->newPatchItemStatementValidator()->validate(
			$this->newUseCaseRequest( [
				'$statementId' => $statementId,
				'$patch' => [ 'valid' => 'patch' ],
				'$editTags' => [],
				'$isBot' => false,
				'$comment' => null,
				'$username' => null,
				'$itemId' => $itemId
			] )
		);

		$this->assertNotNull( $error );
		$this->assertSame( PatchItemStatementValidator::SOURCE_STATEMENT_ID, $error->getSource() );
		$this->assertSame( $statementId, $error->getValue() );
	}

	public function testValidate_withInvalidPatch(): void {
		$invalidPatch = [ 'this is' => 'not a valid patch' ];
		$expectedError = new ValidationError( '', PatchItemStatementValidator::SOURCE_PATCH );
		$this->jsonPatchValidator = $this->createMock( JsonPatchValidator::class );
		$this->jsonPatchValidator->method( 'validate' )
			->with( $invalidPatch, PatchItemStatementValidator::SOURCE_PATCH )
			->willReturn( $expectedError );

		$error = $this->newPatchItemStatementValidator()->validate(
			$this->newUseCaseRequest( [
				'$statementId' => 'Q123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE',
				'$patch' => $invalidPatch,
				'$editTags' => [],
				'$isBot' => false,
				'$comment' => null,
				'$username' => null,
				'$itemId' => null
			] )
		);

		$this->assertSame( $expectedError, $error );
	}

	public function testValidate_withCommentTooLong(): void {
		$comment = str_repeat( 'x', CommentStore::COMMENT_CHARACTER_LIMIT + 1 );
		$error = $this->newPatchItemStatementValidator()->validate(
			$this->newUseCaseRequest( [
				'$statementId' => 'Q123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE',
				'$patch' => [ 'valid' => 'patch' ],
				'$editTags' => [],
				'$isBot' => false,
				'$comment' => $comment,
				'$username' => null,
				'$itemId' => null
			] )
		);

		$this->assertEquals(
			new ValidationError( "500", PatchItemStatementValidator::SOURCE_COMMENT ),
			$error
		);
	}

	public function testValidate_withInvalidEditTags(): void {
		$invalid = 'invalid';
		$error = $this->newPatchItemStatementValidator()->validate(
			$this->newUseCaseRequest( [
				'$statementId' => 'Q123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE',
				'$patch' => [ 'valid' => 'patch' ],
				'$editTags' => [ 'some', 'tags', 'are', $invalid ],
				'$isBot' => false,
				'$comment' => null,
				'$username' => null,
				'$itemId' => null
			] )
		);

		$this->assertEquals(
			new ValidationError( json_encode( $invalid ), PatchItemStatementValidator::SOURCE_EDIT_TAGS ),
			$error
		);
	}

	private function newPatchItemStatementValidator(): PatchItemStatementValidator {
		return new PatchItemStatementValidator(
			new ItemIdValidator(),
			new StatementIdValidator( new ItemIdParser() ),
			$this->jsonPatchValidator,
			new EditMetadataValidator( CommentStore::COMMENT_CHARACTER_LIMIT, self::ALLOWED_TAGS )
		);
	}

	private function newUseCaseRequest( array $requestData ): PatchItemStatementRequest {
		return new PatchItemStatementRequest(
			$requestData['$statementId'],
			$requestData['$patch'],
			$requestData['$editTags'],
			$requestData['$isBot'],
			$requestData['$comment'] ?? null,
			$requestData['$username'] ?? null,
			$requestData['$itemId'] ?? null
		);
	}
}
