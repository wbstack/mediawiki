<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\Application\UseCaseRequestValidation;

use Generator;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Term\Term;
use Wikibase\DataModel\Tests\NewItem;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemLabelEditRequest;
use Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemLabelEditRequestValidatingDeserializer;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Application\Validation\ItemLabelValidator;
use Wikibase\Repo\RestApi\Application\Validation\ValidationError;
use Wikibase\Repo\RestApi\Domain\Services\ItemWriteModelRetriever;
use Wikibase\Repo\Tests\RestApi\Infrastructure\DataAccess\InMemoryItemRepository;

/**
 * @covers \Wikibase\Repo\RestApi\Application\UseCaseRequestValidation\ItemLabelEditRequestValidatingDeserializer
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class ItemLabelEditRequestValidatingDeserializerTest extends TestCase {

	private ItemWriteModelRetriever $itemRetriever;
	private ItemLabelValidator $itemLabelValidator;

	protected function setUp(): void {
		parent::setUp();

		$this->itemRetriever = new InMemoryItemRepository();
		$this->itemRetriever->addItem( NewItem::withId( 'Q123' )->build() );
		$this->itemLabelValidator = $this->createStub( ItemLabelValidator::class );
	}

	public function testGivenValidRequest_returnsLabel(): void {
		$request = $this->createStub( ItemLabelEditRequest::class );
		$request->method( 'getItemId' )->willReturn( 'Q123' );
		$request->method( 'getLanguageCode' )->willReturn( 'en' );
		$request->method( 'getLabel' )->willReturn( 'potato' );

		$this->assertEquals(
			new Term( 'en', 'potato' ),
			$this->newValidatingDeserializer()->validateAndDeserialize( $request )
		);
	}

	public function testGivenItemDoesNotExist_skipsValidation(): void {
		$this->itemRetriever = new InMemoryItemRepository();

		$this->itemLabelValidator = $this->createMock( ItemLabelValidator::class );
		$this->itemLabelValidator->expects( $this->never() )->method( 'validate' );

		$request = $this->createStub( ItemLabelEditRequest::class );
		$request->method( 'getItemId' )->willReturn( 'Q123' );
		$request->method( 'getLanguageCode' )->willReturn( 'en' );
		$request->method( 'getLabel' )->willReturn( 'potato' );

		$this->assertEquals(
			new Term( 'en', 'potato' ),
			$this->newValidatingDeserializer()->validateAndDeserialize( $request )
		);
	}

	public function testGivenLabelIsUnchanged_skipsValidation(): void {
		$itemId = 'Q345';
		$languageCode = 'en';
		$label = 'potato';

		$this->itemRetriever = new InMemoryItemRepository();
		$this->itemRetriever->addItem( NewItem::withId( $itemId )->andLabel( $languageCode, $label )->build() );
		$this->itemLabelValidator = $this->createMock( ItemLabelValidator::class );
		$this->itemLabelValidator->expects( $this->never() )->method( 'validate' );

		$request = $this->createStub( ItemLabelEditRequest::class );
		$request->method( 'getItemId' )->willReturn( $itemId );
		$request->method( 'getLanguageCode' )->willReturn( $languageCode );
		$request->method( 'getLabel' )->willReturn( $label );

		$this->assertEquals(
			new Term( $languageCode, $label ),
			$this->newValidatingDeserializer()->validateAndDeserialize( $request )
		);
	}

	/**
	 * @dataProvider invalidLabelProvider
	 */
	public function testWithInvalidLabel(
		ValidationError $validationError,
		string $expectedErrorCode,
		string $expectedErrorMessage,
		array $expectedContext = []
	): void {
		$request = $this->createStub( ItemLabelEditRequest::class );
		$request->method( 'getItemId' )->willReturn( 'Q123' );
		$request->method( 'getLanguageCode' )->willReturn( 'en' );
		$request->method( 'getLabel' )->willReturn( 'my label' );

		$this->itemLabelValidator = $this->createStub( ItemLabelValidator::class );
		$this->itemLabelValidator->method( 'validate' )->willReturn( $validationError );

		try {
			$this->newValidatingDeserializer()->validateAndDeserialize( $request );
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $error ) {
			$this->assertSame( $expectedErrorCode, $error->getErrorCode() );
			$this->assertSame( $expectedErrorMessage, $error->getErrorMessage() );
			$this->assertSame( $expectedContext, $error->getErrorContext() );
		}
	}

	public static function invalidLabelProvider(): Generator {
		$label = "tab characters \t not allowed";
		yield 'invalid label' => [
			new ValidationError(
				ItemLabelValidator::CODE_INVALID,
				[ ItemLabelValidator::CONTEXT_LABEL => $label ],
			),
			UseCaseError::INVALID_VALUE,
			"Invalid value at '/label'",
			[ UseCaseError::CONTEXT_PATH => '/label' ],
		];

		yield 'label empty' => [
			new ValidationError( ItemLabelValidator::CODE_EMPTY ),
			UseCaseError::INVALID_VALUE,
			"Invalid value at '/label'",
			[ UseCaseError::CONTEXT_PATH => '/label' ],
		];

		$limit = 250;
		yield 'label too long' => [
			new ValidationError( ItemLabelValidator::CODE_TOO_LONG, [
				ItemLabelValidator::CONTEXT_LABEL => 'This label is too long.',
				ItemLabelValidator::CONTEXT_LIMIT => $limit,
			] ),
			UseCaseError::VALUE_TOO_LONG,
			'The input value is too long',
			[
				UseCaseError::CONTEXT_PATH => '/label',
				UseCaseError::CONTEXT_LIMIT => $limit,
			],
		];

		$language = 'en';
		yield 'label equals description' => [
			new ValidationError(
				ItemLabelValidator::CODE_LABEL_SAME_AS_DESCRIPTION,
				[ ItemLabelValidator::CONTEXT_LANGUAGE => $language ]
			),
			UseCaseError::DATA_POLICY_VIOLATION,
			'Edit violates data policy',
			[
				UseCaseError::CONTEXT_VIOLATION => UseCaseError::POLICY_VIOLATION_LABEL_DESCRIPTION_SAME_VALUE,
				UseCaseError::CONTEXT_VIOLATION_CONTEXT => [ UseCaseError::CONTEXT_LANGUAGE => $language ],
			],
		];

		$language = 'en';
		$conflictingItemId = 'Q456';
		yield 'label/description not unique' => [
			new ValidationError( ItemLabelValidator::CODE_LABEL_DESCRIPTION_DUPLICATE, [
				ItemLabelValidator::CONTEXT_LANGUAGE => $language,
				ItemLabelValidator::CONTEXT_LABEL => 'My Label',
				ItemLabelValidator::CONTEXT_DESCRIPTION => 'My Description',
				ItemLabelValidator::CONTEXT_CONFLICTING_ITEM_ID => $conflictingItemId,
			] ),
			UseCaseError::DATA_POLICY_VIOLATION,
			'Edit violates data policy',
			[
				UseCaseError::CONTEXT_VIOLATION => UseCaseError::POLICY_VIOLATION_ITEM_LABEL_DESCRIPTION_DUPLICATE,
				UseCaseError::CONTEXT_VIOLATION_CONTEXT => [
					UseCaseError::CONTEXT_LANGUAGE => $language,
					UseCaseError::CONTEXT_CONFLICTING_ITEM_ID => $conflictingItemId,
				],
			],
		];
	}

	private function newValidatingDeserializer(): ItemLabelEditRequestValidatingDeserializer {
		return new ItemLabelEditRequestValidatingDeserializer(
			$this->itemLabelValidator,
			$this->itemRetriever
		);
	}
}
