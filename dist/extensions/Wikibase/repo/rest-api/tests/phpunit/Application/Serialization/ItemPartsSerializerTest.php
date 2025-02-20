<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\Application\Serialization;

use ArrayObject;
use Generator;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Repo\RestApi\Application\Serialization\AliasesSerializer;
use Wikibase\Repo\RestApi\Application\Serialization\DescriptionsSerializer;
use Wikibase\Repo\RestApi\Application\Serialization\ItemPartsSerializer;
use Wikibase\Repo\RestApi\Application\Serialization\LabelsSerializer;
use Wikibase\Repo\RestApi\Application\Serialization\SitelinksSerializer;
use Wikibase\Repo\RestApi\Application\Serialization\StatementListSerializer;
use Wikibase\Repo\RestApi\Domain\ReadModel\Aliases;
use Wikibase\Repo\RestApi\Domain\ReadModel\AliasesInLanguage;
use Wikibase\Repo\RestApi\Domain\ReadModel\Description;
use Wikibase\Repo\RestApi\Domain\ReadModel\Descriptions;
use Wikibase\Repo\RestApi\Domain\ReadModel\ItemParts;
use Wikibase\Repo\RestApi\Domain\ReadModel\ItemPartsBuilder;
use Wikibase\Repo\RestApi\Domain\ReadModel\Label;
use Wikibase\Repo\RestApi\Domain\ReadModel\Labels;
use Wikibase\Repo\RestApi\Domain\ReadModel\Sitelinks;
use Wikibase\Repo\RestApi\Domain\ReadModel\StatementList;

/**
 * @covers \Wikibase\Repo\RestApi\Application\Serialization\ItemPartsSerializer
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class ItemPartsSerializerTest extends TestCase {

	private LabelsSerializer $labelsSerializer;
	private DescriptionsSerializer $descriptionsSerializer;
	private AliasesSerializer $aliasesSerializer;
	private StatementListSerializer $statementsSerializer;
	private SitelinksSerializer $sitelinksSerializer;

	protected function setUp(): void {
		$this->labelsSerializer = $this->createStub( LabelsSerializer::class );
		$this->descriptionsSerializer = $this->createStub( DescriptionsSerializer::class );
		$this->aliasesSerializer = $this->createStub( AliasesSerializer::class );
		$this->statementsSerializer = $this->createStub( StatementListSerializer::class );
		$this->sitelinksSerializer = $this->createStub( SitelinksSerializer::class );
	}

	public function testSerializeId(): void {
		$itemParts = ( new ItemPartsBuilder( new ItemId( 'Q123' ), [] ) )
			->build();

		$serialization = $this->newSerializer()->serialize( $itemParts );

		$this->assertSame( $serialization['id'], $itemParts->getId()->getSerialization() );
	}

	public function testSerializeType(): void {
		$itemParts = $this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_TYPE ] )->build();

		$serialization = $this->newSerializer()->serialize( $itemParts );

		$this->assertSame( Item::ENTITY_TYPE, $serialization['type'] );
	}

	public function testSerializeLabels(): void {
		$enLabel = 'potato';
		$koLabel = '감자';
		$expectedSerialization = new ArrayObject( [
			[ 'en' => $enLabel ], [ 'de' => $koLabel ],
		] );
		$this->labelsSerializer = $this->createStub( LabelsSerializer::class );
		$this->labelsSerializer
			->method( 'serialize' )
			->willReturn( $expectedSerialization );

		$itemParts = $this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_LABELS ] )
			->setLabels( new Labels(
				new Label( 'en', $enLabel ),
				new Label( 'ko', $koLabel ),
			) )
			->build();

		$serialization = $this->newSerializer()->serialize( $itemParts );

		$this->assertSame( $expectedSerialization, $serialization['labels'] );
	}

	public function testSerializeDescriptions(): void {
		$enDescription = 'root vegetable';
		$deDescription = 'Art der Gattung Nachtschatten (Solanum)';
		$expectedSerialization = new ArrayObject( [
			[ 'en' => $enDescription ],
			[ 'de' => $deDescription ],
		] );
		$this->descriptionsSerializer = $this->createStub( DescriptionsSerializer::class );
		$this->descriptionsSerializer
			->method( 'serialize' )
			->willReturn( $expectedSerialization );

		$itemParts = $this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_DESCRIPTIONS ] )
			->setDescriptions( new Descriptions(
				new Description( 'en', $enDescription ),
				new Description( 'de', $deDescription ),
			) )
			->build();

		$serialization = $this->newSerializer()->serialize( $itemParts );

		$this->assertSame( $expectedSerialization, $serialization['descriptions'] );
	}

	public function testSerializeAliases(): void {
		$enAliases = [ 'spud', 'tater' ];
		$deAliases = [ 'Erdapfel' ];
		$itemParts = $this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_ALIASES ] )
			->setAliases( new Aliases(
				new AliasesInLanguage( 'en', $enAliases ),
				new AliasesInLanguage( 'de', $deAliases ),
			) )
			->build();

		$expectedAliasesSerialization = new ArrayObject( [ 'en' => $enAliases, 'de' => $deAliases ] );
		$this->aliasesSerializer = $this->createStub( AliasesSerializer::class );
		$this->aliasesSerializer->method( 'serialize' )->willReturn( $expectedAliasesSerialization );

		$serialization = $this->newSerializer()->serialize( $itemParts );

		$this->assertSame( $expectedAliasesSerialization, $serialization['aliases'] );
	}

	public function testSerializeStatements(): void {
		$statements = $this->createStub( StatementList::class );
		$expectedSerialization = new ArrayObject( [ 'some' => 'serialization' ] );

		$itemParts = $this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_STATEMENTS ] )
			->setStatements( $statements )
			->build();

		$this->statementsSerializer = $this->createMock( StatementListSerializer::class );
		$this->statementsSerializer->expects( $this->once() )
			->method( 'serialize' )
			->with( $statements )
			->willReturn( $expectedSerialization );

		$serialization = $this->newSerializer()->serialize( $itemParts );

		$this->assertSame( $expectedSerialization, $serialization['statements'] );
	}

	public function testSerializeSitelinks(): void {
		$sitelinks = $this->createStub( Sitelinks::class );
		$expectedSerialization = new ArrayObject( [ 'some' => 'serialization' ] );

		$itemParts = $this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_SITELINKS ] )
			->setSitelinks( $sitelinks )
			->build();

		$this->sitelinksSerializer = $this->createMock( SitelinksSerializer::class );
		$this->sitelinksSerializer->expects( $this->once() )
			->method( 'serialize' )
			->with( $sitelinks )
			->willReturn( $expectedSerialization );

		$serialization = $this->newSerializer()->serialize( $itemParts );

		$this->assertSame( $expectedSerialization, $serialization['sitelinks'] );
	}

	/**
	 * @dataProvider itemPartsFieldsProvider
	 */
	public function testSkipsFieldsThatAreNotSet( ItemParts $itemParts, array $fields ): void {
		$serialization = $this->newSerializer()->serialize( $itemParts );
		$serializationFields = array_keys( $serialization );

		$this->assertEqualsCanonicalizing( $fields, $serializationFields );
	}

	public function itemPartsFieldsProvider(): Generator {
		yield [
			$this->newItemPartsBuilderWithSomeId( [] )->build(),
			[ 'id' ],
		];
		yield [
			$this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_TYPE ] )->build(),
			[ 'id', 'type' ],
		];
		yield [
			$this->newItemPartsBuilderWithSomeId(
				[ ItemParts::FIELD_LABELS, ItemParts::FIELD_DESCRIPTIONS, ItemParts::FIELD_ALIASES ]
			)
				->setLabels( new Labels() )
				->setDescriptions( new Descriptions() )
				->setAliases( new Aliases() )
				->build(),
			[ 'id', 'labels', 'descriptions', 'aliases' ],
		];
		yield [
			$this->newItemPartsBuilderWithSomeId( [ ItemParts::FIELD_STATEMENTS ] )
				->setStatements( new StatementList() )
				->build(),
			[ 'id', 'statements' ],
		];
		yield [
			$this->newItemPartsBuilderWithSomeId( ItemParts::VALID_FIELDS )
				->setLabels( new Labels() )
				->setDescriptions( new Descriptions() )
				->setAliases( new Aliases() )
				->setStatements( new StatementList() )
				->setSitelinks( new Sitelinks() )
				->build(),
			[ 'id', 'type', 'labels', 'descriptions', 'aliases', 'statements', 'sitelinks' ],
		];
	}

	private function newSerializer(): ItemPartsSerializer {
		return new ItemPartsSerializer(
			$this->labelsSerializer,
			$this->descriptionsSerializer,
			$this->aliasesSerializer,
			$this->statementsSerializer,
			$this->sitelinksSerializer
		);
	}

	private function newItemPartsBuilderWithSomeId( array $requestedFields ): ItemPartsBuilder {
		return new ItemPartsBuilder( new ItemId( 'Q666' ), $requestedFields );
	}

}
