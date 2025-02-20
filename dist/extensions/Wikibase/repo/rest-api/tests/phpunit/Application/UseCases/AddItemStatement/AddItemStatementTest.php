<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\Application\UseCases\AddItemStatement;

use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\Statement\GuidGenerator;
use Wikibase\DataModel\Statement\StatementGuid;
use Wikibase\DataModel\Tests\NewItem;
use Wikibase\Repo\RestApi\Application\UseCases\AddItemStatement\AddItemStatement;
use Wikibase\Repo\RestApi\Application\UseCases\AddItemStatement\AddItemStatementRequest;
use Wikibase\Repo\RestApi\Application\UseCases\AddItemStatement\AddItemStatementResponse;
use Wikibase\Repo\RestApi\Application\UseCases\AssertItemExists;
use Wikibase\Repo\RestApi\Application\UseCases\AssertUserIsAuthorized;
use Wikibase\Repo\RestApi\Application\UseCases\GetLatestItemRevisionMetadata;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseException;
use Wikibase\Repo\RestApi\Domain\Model\EditMetadata;
use Wikibase\Repo\RestApi\Domain\Model\StatementEditSummary;
use Wikibase\Repo\RestApi\Domain\Model\User;
use Wikibase\Repo\RestApi\Domain\Services\ItemUpdater;
use Wikibase\Repo\RestApi\Domain\Services\ItemWriteModelRetriever;
use Wikibase\Repo\Tests\RestApi\Application\UseCaseRequestValidation\TestValidatingRequestDeserializer;
use Wikibase\Repo\Tests\RestApi\Domain\ReadModel\NewStatementReadModel;
use Wikibase\Repo\Tests\RestApi\Infrastructure\DataAccess\InMemoryItemRepository;

/**
 * @covers \Wikibase\Repo\RestApi\Application\UseCases\AddItemStatement\AddItemStatement
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class AddItemStatementTest extends TestCase {

	private GetLatestItemRevisionMetadata $getRevisionMetadata;
	private ItemWriteModelRetriever $itemRetriever;
	private ItemUpdater $itemUpdater;
	private GuidGenerator $guidGenerator;
	private AssertUserIsAuthorized $assertUserIsAuthorized;

	protected function setUp(): void {
		parent::setUp();

		$this->getRevisionMetadata = $this->createStub( GetLatestItemRevisionMetadata::class );
		$this->getRevisionMetadata->method( 'execute' )->willReturn( [ 321, '20201111070707' ] );
		$this->itemRetriever = $this->createStub( ItemWriteModelRetriever::class );
		$this->itemUpdater = $this->createStub( ItemUpdater::class );
		$this->guidGenerator = $this->createStub( GuidGenerator::class );
		$this->assertUserIsAuthorized = $this->createStub( AssertUserIsAuthorized::class );
	}

	public function testAddStatement(): void {
		$item = NewItem::withId( 'Q123' )->build();
		$newGuid = new StatementGuid( $item->getId(), 'AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE' );
		[ $statementReadModel, $statementWriteModel ] = NewStatementReadModel::noValueFor(
			TestValidatingRequestDeserializer::EXISTING_STRING_PROPERTY
		)->withGuid( $newGuid )->buildReadAndWriteModel();
		$editTags = [ TestValidatingRequestDeserializer::ALLOWED_TAGS[0] ];
		$isBot = false;
		$comment = 'potato';

		$this->guidGenerator = $this->createStub( GuidGenerator::class );
		$this->guidGenerator->method( 'newStatementId' )->willReturn( $newGuid );

		$itemRepo = new InMemoryItemRepository();
		$itemRepo->addItem( $item );
		$this->itemRetriever = $itemRepo;
		$this->itemUpdater = $itemRepo;

		$response = $this->newUseCase()->execute(
			new AddItemStatementRequest(
				$item->getId()->getSerialization(),
				$this->getValidNoValueStatementSerialization(),
				$editTags,
				$isBot,
				$comment,
				null
			)
		);

		$this->assertInstanceOf( AddItemStatementResponse::class, $response );
		$this->assertEquals(
			$statementReadModel,
			$response->getStatement()
		);
		$this->assertSame( $itemRepo->getLatestRevisionId( $item->getId() ), $response->getRevisionId() );
		$this->assertSame( $itemRepo->getLatestRevisionTimestamp( $item->getId() ), $response->getLastModified() );
		$this->assertEquals(
			new EditMetadata( $editTags, $isBot, StatementEditSummary::newAddSummary( $comment, $statementWriteModel ) ),
			$itemRepo->getLatestRevisionEditMetadata( $item->getId() )
		);
	}

	public function testGivenItemNotFoundOrRedirect_throws(): void {
		$itemId = 'Q321';
		$expectedException = $this->createStub( UseCaseException::class );

		$this->getRevisionMetadata = $this->createStub( GetLatestItemRevisionMetadata::class );
		$this->getRevisionMetadata->method( 'execute' )->willThrowException( $expectedException );

		try {
			$this->newUseCase()->execute(
				new AddItemStatementRequest(
					$itemId,
					$this->getValidNoValueStatementSerialization(),
					[],
					false,
					null,
					null
				)
			);
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseException $e ) {
			$this->assertSame( $expectedException, $e );
		}
	}

	public function testValidationError_throwsUseCaseError(): void {
		try {
			$this->newUseCase()->execute(
				new AddItemStatementRequest( 'X123', [], [], false, null, null )
			);
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $e ) {
			$this->assertSame( UseCaseError::INVALID_PATH_PARAMETER, $e->getErrorCode() );
		}
	}

	public function testProtectedItem_throwsUseCaseError(): void {
		$itemId = new ItemId( 'Q123' );

		$expectedError = new UseCaseError(
			UseCaseError::PERMISSION_DENIED_UNKNOWN_REASON,
			'You have no permission to edit this item.'
		);
		$this->assertUserIsAuthorized = $this->createMock( AssertUserIsAuthorized::class );
		$this->assertUserIsAuthorized->method( 'checkEditPermissions' )
			->with( $itemId, User::newAnonymous() )
			->willThrowException( $expectedError );

		try {
			$request = new AddItemStatementRequest(
				$itemId->getSerialization(),
				$this->getValidNoValueStatementSerialization(),
				[],
				false,
				null,
				null
			);
			$this->newUseCase()->execute( $request );
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $e ) {
			$this->assertSame( $expectedError, $e );
		}
	}

	private function newUseCase(): AddItemStatement {
		return new AddItemStatement(
			new TestValidatingRequestDeserializer(),
			new AssertItemExists( $this->getRevisionMetadata ),
			$this->itemRetriever,
			$this->itemUpdater,
			$this->guidGenerator,
			$this->assertUserIsAuthorized
		);
	}

	private function getValidNoValueStatementSerialization(): array {
		return [
			'property' => [
				'id' => TestValidatingRequestDeserializer::EXISTING_STRING_PROPERTY,
			],
			'value' => [
				'type' => 'novalue',
			],
		];
	}

}
