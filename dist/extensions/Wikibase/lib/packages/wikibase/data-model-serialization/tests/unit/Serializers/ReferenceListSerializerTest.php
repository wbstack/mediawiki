<?php

declare( strict_types = 1 );

namespace Tests\Wikibase\DataModel\Serializers;

use Serializers\DispatchableSerializer;
use Serializers\Serializer;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\ReferenceList;
use Wikibase\DataModel\Serializers\ReferenceListSerializer;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;

/**
 * @covers Wikibase\DataModel\Serializers\ReferenceListSerializer
 *
 * @license GPL-2.0-or-later
 * @author Thomas Pellissier Tanon
 */
class ReferenceListSerializerTest extends DispatchableSerializerTestCase {

	protected function buildSerializer(): DispatchableSerializer {
		$referenceSerializerFake = $this->createMock( Serializer::class );
		$referenceSerializerFake->expects( $this->any() )
			->method( 'serialize' )
			->willReturn( [
				'hash' => 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
				'snaks' => [],
			] );

		return new ReferenceListSerializer( $referenceSerializerFake );
	}

	public function serializableProvider(): array {
		return [
			[
				new ReferenceList(),
			],
			[
				new ReferenceList( [
					new Reference(),
				] ),
			],
		];
	}

	public function nonSerializableProvider(): array {
		return [
			[
				5,
			],
			[
				[],
			],
			[
				new Reference(),
			],
		];
	}

	public function serializationProvider(): array {
		return [
			[
				[],
				new ReferenceList(),
			],
			[
				[
					[
						'hash' => 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
						'snaks' => [],
					],
					[
						'hash' => 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
						'snaks' => [],
					],
				],
				new ReferenceList( [
					new Reference( [ new PropertyNoValueSnak( 1 ) ] ),
					new Reference( [ new PropertyNoValueSnak( 1 ) ] ),
				] ),
			],
		];
	}

}
