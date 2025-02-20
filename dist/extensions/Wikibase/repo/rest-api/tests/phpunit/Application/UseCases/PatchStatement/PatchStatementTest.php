<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\Application\UseCases\PatchStatement;

use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\DataModel\Exception\PropertyChangedException;
use Wikibase\DataModel\Statement\StatementGuid;
use Wikibase\DataModel\Tests\NewStatement;
use Wikibase\Repo\RestApi\Application\Serialization\PropertyValuePairSerializer;
use Wikibase\Repo\RestApi\Application\Serialization\ReferenceSerializer;
use Wikibase\Repo\RestApi\Application\Serialization\StatementSerializer;
use Wikibase\Repo\RestApi\Application\UseCases\AssertStatementSubjectExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertUserIsAuthorized;
use Wikibase\Repo\RestApi\Application\UseCases\GetLatestStatementSubjectRevisionMetadata;
use Wikibase\Repo\RestApi\Application\UseCases\PatchJson;
use Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchedStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchStatement;
use Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchStatementRequest;
use Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchStatementResponse;
use Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchStatementValidator;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Domain\Model\EditMetadata;
use Wikibase\Repo\RestApi\Domain\Model\StatementEditSummary;
use Wikibase\Repo\RestApi\Domain\Model\User;
use Wikibase\Repo\RestApi\Domain\Services\StatementReadModelConverter;
use Wikibase\Repo\RestApi\Domain\Services\StatementRetriever;
use Wikibase\Repo\RestApi\Domain\Services\StatementUpdater;
use Wikibase\Repo\RestApi\Infrastructure\JsonDiffJsonPatcher;
use Wikibase\Repo\Tests\RestApi\Application\UseCaseRequestValidation\TestValidatingRequestDeserializer;
use Wikibase\Repo\Tests\RestApi\Domain\ReadModel\NewStatementReadModel;
use Wikibase\Repo\Tests\RestApi\Infrastructure\DataAccess\InMemoryStatementRepository;
use Wikibase\Repo\Tests\RestApi\Infrastructure\DataAccess\StatementReadModelHelper;

/**
 * @covers \Wikibase\Repo\RestApi\Application\UseCases\PatchStatement\PatchStatement
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class PatchStatementTest extends TestCase {

	use StatementReadModelHelper;

	private const STRING_PROPERTY = 'P123';

	private PatchStatementValidator $useCaseValidator;
	private PatchedStatementValidator $patchedStatementValidator;
	private StatementSerializer $statementSerializer;
	private StatementRetriever $statementRetriever;
	private StatementUpdater $statementUpdater;
	private GetLatestStatementSubjectRevisionMetadata $getRevisionMetadata;
	private AssertUserIsAuthorized $assertUserIsAuthorized;
	private StatementReadModelConverter $statementReadModelConverter;

	protected function setUp(): void {
		parent::setUp();

		$this->useCaseValidator = new TestValidatingRequestDeserializer();
		$this->patchedStatementValidator = $this->createStub( PatchedStatementValidator::class );
		$this->statementRetriever = $this->createStub( StatementRetriever::class );
		$this->statementUpdater = $this->createStub( StatementUpdater::class );
		$this->getRevisionMetadata = $this->createStub( GetLatestStatementSubjectRevisionMetadata::class );
		$this->getRevisionMetadata->method( 'execute' )->willReturn( [ 456, '20221111070607' ] );
		$this->assertUserIsAuthorized = $this->createStub( AssertUserIsAuthorized::class );
		$this->statementSerializer = $this->newStatementSerializer();
		$this->statementReadModelConverter = $this->newStatementReadModelConverter();
	}

	/**
	 * @dataProvider provideSubjectId
	 */
	public function testPatchStatement_success( EntityId $subjectId ): void {
		$statementId = new StatementGuid( $subjectId, 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE' );
		$oldStatementValue = 'old statement value';
		$newStatementValue = 'new statement value';

		$statementToPatch = NewStatement::forProperty( self::STRING_PROPERTY )
			->withGuid( $statementId )
			->withValue( $oldStatementValue )
			->build();

		$editTags = TestValidatingRequestDeserializer::ALLOWED_TAGS;
		$isBot = false;
		$comment = 'statement replaced by ' . __METHOD__;

		$patch = $this->getValidValueReplacingPatch( $newStatementValue );

		$patchedStatement = NewStatement::forProperty( self::STRING_PROPERTY )
			->withGuid( $statementId )
			->withValue( $newStatementValue )
			->build();

		$readModelPatchedStatement = $this->statementReadModelConverter->convert( $patchedStatement );

		$statementsRepo = new InMemoryStatementRepository();
		$statementsRepo->addStatement( $statementToPatch );
		$this->statementRetriever = $statementsRepo;
		$this->statementUpdater = $statementsRepo;

		$this->patchedStatementValidator = $this->createStub( PatchedStatementValidator::class );
		$this->patchedStatementValidator->method( 'validateAndDeserializeStatement' )->willReturn( $patchedStatement );

		$response = $this->newUseCase()->execute(
			$this->newUseCaseRequest( [
				'$statementId' => (string)$statementId,
				'$patch' => $patch,
				'$editTags' => $editTags,
				'$isBot' => $isBot,
				'$comment' => $comment,
				'$username' => null,
			] )
		);

		$this->assertInstanceOf( PatchStatementResponse::class, $response );
		$this->assertEquals( $readModelPatchedStatement, $response->getStatement() );
		$this->assertSame( $statementsRepo->getLatestRevisionTimestamp( $statementId ), $response->getLastModified() );
		$this->assertSame( $statementsRepo->getLatestRevisionId( $statementId ), $response->getRevisionId() );
		$this->assertEquals(
			new EditMetadata( $editTags, $isBot, StatementEditSummary::newPatchSummary(
				$comment,
				$patchedStatement
			) ),
			$statementsRepo->getLatestRevisionEditMetadata( $statementId )
		);
	}

	/**
	 * @dataProvider provideSubjectId
	 */
	public function testStatementNotFoundOnSubject_throwsUseCaseError( EntityId $subjectId ): void {
		try {
			$this->newUseCase()->execute(
				$this->newUseCaseRequest( [
					'$statementId' => "$subjectId\$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE",
					'$patch' => $this->getValidValueReplacingPatch(),
				] )
			);
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $e ) {
			$this->assertEquals( UseCaseError::newResourceNotFound( 'statement' ), $e );
		}
	}

	/**
	 * @dataProvider provideSubjectId
	 */
	public function testRejectsPropertyIdChange( EntityId $subjectId ): void {
		$guid = $subjectId->getSerialization() . '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
		$statementToPatch = NewStatementReadModel::noValueFor( self::STRING_PROPERTY )
			->withGuid( $guid )
			->build();

		$patchedStatement = NewStatement::noValueFor( 'P321' )->withGuid( $guid )->build();

		$this->statementRetriever->method( 'getStatement' )->willReturn( $statementToPatch );
		$this->patchedStatementValidator->method( 'validateAndDeserializeStatement' )->willReturn( $patchedStatement );

		$this->statementUpdater = $this->createStub( StatementUpdater::class );
		$this->statementUpdater->method( 'update' )->willThrowException( new PropertyChangedException() );

		try {
			$this->newUseCase()->execute(
				$this->newUseCaseRequest( [
					'$statementId' => $guid,
					'$patch' => [ [ 'op' => 'replace', 'path' => '/property/id', 'value' => 'P321' ] ],
				] )
			);
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $e ) {
			$this->assertSame( UseCaseError::PATCH_RESULT_MODIFIED_READ_ONLY_VALUE, $e->getErrorCode() );
			$this->assertSame( 'Read only value in patch result cannot be modified', $e->getErrorMessage() );
			$this->assertSame( [ 'path' => '/property/id' ], $e->getErrorContext() );
		}
	}

	/**
	 * @dataProvider provideSubjectId
	 */
	public function testRejectsStatementIdChange( EntityId $subjectId ): void {
		$originalGuid = $subjectId->getSerialization() . '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
		$newGuid = $subjectId->getSerialization() . '$FFFFFFFF-BBBB-CCCC-DDDD-EEEEEEEEEEEE';

		$statementToPatch = NewStatementReadModel::noValueFor( self::STRING_PROPERTY )
			->withGuid( $originalGuid )
			->build();

		$patchedStatement = NewStatement::noValueFor( self::STRING_PROPERTY )->withGuid( $newGuid )->build();

		$this->statementRetriever->method( 'getStatement' )->willReturn( $statementToPatch );
		$this->patchedStatementValidator->method( 'validateAndDeserializeStatement' )->willReturn( $patchedStatement );

		try {
			$this->newUseCase()->execute(
				$this->newUseCaseRequest( [
					'$statementId' => $originalGuid,
					'$patch' => [ [ 'op' => 'replace', 'path' => '/id', 'value' => $newGuid ] ],
				] )
			);
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $e ) {
			$this->assertSame( UseCaseError::PATCH_RESULT_MODIFIED_READ_ONLY_VALUE, $e->getErrorCode() );
			$this->assertSame( 'Read only value in patch result cannot be modified', $e->getErrorMessage() );
			$this->assertSame( [ 'path' => '/id' ], $e->getErrorContext() );
		}
	}

	/**
	 * @dataProvider inapplicablePatchProvider
	 */
	public function testGivenValidInapplicablePatch_throwsUseCaseError(
		array $patch,
		string $expectedErrorCode,
		array $subjectIds
	): void {
		foreach ( $subjectIds as $subjectId ) {
			$statementId = new StatementGuid( $subjectId, 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE' );

			$this->statementRetriever->method( 'getStatement' )->willReturn(
				NewStatementReadModel::forProperty( self::STRING_PROPERTY )
					->withGuid( $statementId )
					->withValue( 'abc' )
					->build()
			);

			try {
				$this->newUseCase()->execute(
					$this->newUseCaseRequest( [ '$statementId' => "$statementId", '$patch' => $patch ] )
				);
				$this->fail( 'this should not be reached' );
			} catch ( UseCaseError $e ) {
				$this->assertSame( $expectedErrorCode, $e->getErrorCode() );
			}
		}
	}

	public static function inapplicablePatchProvider(): Generator {
		yield 'patch test operation failed' => [
			[
				[
					'op' => 'test',
					'path' => '/value/content',
					'value' => 'these are not the droids you are looking for',
				],
			],
			UseCaseError::PATCH_TEST_FAILED,
			[ new ItemId( 'Q123' ), new NumericPropertyId( 'P123' ) ],
		];

		yield 'non-existent path' => [
			[
				[
					'op' => 'remove',
					'path' => '/this/path/does/not/exist',
				],
			],
			UseCaseError::PATCH_TARGET_NOT_FOUND,
			[ new ItemId( 'Q123' ), new NumericPropertyId( 'P123' ) ],
		];
	}

	/**
	 * @dataProvider provideSubjectId
	 */
	public function testGivenPatchedStatementInvalid_throwsUseCaseError( EntityId $subjectId ): void {
		$patch = [
			[
				'op' => 'remove',
				'path' => '/property',
			],
		];

		$statementId = new StatementGuid( $subjectId, 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE' );

		$this->statementRetriever->method( 'getStatement' )->willReturn(
			NewStatementReadModel::forProperty( self::STRING_PROPERTY )
				->withGuid( $statementId )
				->withValue( 'abc' )
				->build()
		);

		$expectedException = $this->createStub( UseCaseError::class );

		$this->patchedStatementValidator->method( 'validateAndDeserializeStatement' )->willThrowException( $expectedException );

		try {
			$this->newUseCase()->execute(
				$this->newUseCaseRequest( [ '$statementId' => "$statementId", '$patch' => $patch ] )
			);

			$this->fail( 'this should not be reached' );
		} catch ( Exception $e ) {
			$this->assertSame( $expectedException, $e );
		}
	}

	/**
	 * @dataProvider provideSubjectId
	 */
	public function testGivenProtectedStatementSubject_throwsUseCaseError( EntityId $subjectId ): void {
		$statementId = "$subjectId\$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE";
		$statementReadModel = NewStatementReadModel::forProperty( self::STRING_PROPERTY )
			->withGuid( $statementId )
			->withValue( 'abc' )
			->build();

		$expectedError = $this->createStub( UseCaseError::class );

		$this->assertUserIsAuthorized = $this->createMock( AssertUserIsAuthorized::class );
		$this->assertUserIsAuthorized->method( 'checkEditPermissions' )
			->with( $subjectId, User::newAnonymous() )
			->willThrowException( $expectedError );

		$this->statementRetriever->method( 'getStatement' )->willReturn( $statementReadModel );

		try {
			$this->newUseCase()->execute(
				$this->newUseCaseRequest( [
					'$statementId' => $statementId,
					'$patch' => $this->getValidValueReplacingPatch(),
				] )
			);
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $e ) {
			$this->assertSame( $expectedError, $e );
		}
	}

	public function provideSubjectId(): Generator {
		yield 'item id' => [ new ItemId( 'Q123' ) ];
		yield 'property id' => [ new NumericPropertyId( 'P123' ) ];
	}

	private function newUseCase(): PatchStatement {
		return new PatchStatement(
			$this->useCaseValidator,
			$this->patchedStatementValidator,
			new PatchJson( new JsonDiffJsonPatcher() ),
			$this->statementSerializer,
			new AssertStatementSubjectExists( $this->getRevisionMetadata ),
			$this->statementRetriever,
			$this->statementUpdater,
			$this->assertUserIsAuthorized
		);
	}

	private function newUseCaseRequest( array $requestData ): PatchStatementRequest {
		return new PatchStatementRequest(
			$requestData['$statementId'],
			$requestData['$patch'],
			$requestData['$editTags'] ?? [],
			$requestData['$isBot'] ?? false,
			$requestData['$comment'] ?? null,
			$requestData['$username'] ?? null
		);
	}

	private function getValidValueReplacingPatch( string $newStatementValue = '' ): array {
		return [
			[
				'op' => 'replace',
				'path' => '/value/content',
				'value' => $newStatementValue,
			],
		];
	}

	private function newStatementSerializer(): StatementSerializer {
		$propertyValuePairSerializer = new PropertyValuePairSerializer();

		return new StatementSerializer(
			$propertyValuePairSerializer,
			new ReferenceSerializer( $propertyValuePairSerializer )
		);
	}

}
