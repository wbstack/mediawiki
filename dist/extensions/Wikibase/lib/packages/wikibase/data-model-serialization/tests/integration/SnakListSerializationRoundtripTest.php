<?php

namespace Tests\Wikibase\DataModel;

use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Deserializers\DeserializerFactory;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\DataModel\Serializers\SerializerFactory;
use Wikibase\DataModel\Services\Lookup\InMemoryDataTypeLookup;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Snak\SnakList;

/**
 * @covers DataValues\Serializers\DataValueSerializer
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thomas Pellissier Tanon
 */
class SnakListSerializationRoundtripTest extends TestCase {

	/**
	 * @dataProvider snakListProvider
	 */
	public function testSnakSerializationRoundtrips( SnakList $snaks ) {
		$serializerFactory = new SerializerFactory( new DataValueSerializer() );
		$deserializerFactory = new DeserializerFactory(
			new DataValueDeserializer(),
			new BasicEntityIdParser(),
			new InMemoryDataTypeLookup(),
			[],
			[]
		);

		$serialization = $serializerFactory->newSnakListSerializer()->serialize( $snaks );
		$newSnaks = $deserializerFactory->newSnakListDeserializer()->deserialize( $serialization );
		$this->assertEquals( $snaks, $newSnaks );
	}

	public static function snakListProvider() {
		return [
			[
				new SnakList( [] ),
			],
			[
				new SnakList( [
					new PropertyNoValueSnak( 42 ),
				] ),
			],
			[
				new SnakList( [
					new PropertyNoValueSnak( 42 ),
					new PropertyNoValueSnak( 43 ),
				] ),
			],
			[
				new SnakList( [
					new PropertyNoValueSnak( 42 ),
					new PropertySomeValueSnak( 42 ),
					new PropertyNoValueSnak( 43 ),
				] ),
			],
		];
	}

}
