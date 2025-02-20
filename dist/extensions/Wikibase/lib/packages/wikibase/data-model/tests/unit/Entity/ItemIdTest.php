<?php

namespace Wikibase\DataModel\Tests\Entity;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers \Wikibase\DataModel\Entity\ItemId
 *
 * @group Wikibase
 * @group WikibaseDataModel
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ItemIdTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider idSerializationProvider
	 */
	public function testCanConstructId( $idSerialization, $normalizedIdSerialization ) {
		$id = new ItemId( $idSerialization );

		$this->assertSame(
			$normalizedIdSerialization,
			$id->getSerialization()
		);
	}

	public static function idSerializationProvider() {
		return [
			[ 'q1', 'Q1' ],
			[ 'q100', 'Q100' ],
			[ 'q1337', 'Q1337' ],
			[ 'q31337', 'Q31337' ],
			[ 'Q31337', 'Q31337' ],
			[ 'Q42', 'Q42' ],
			[ 'Q2147483647', 'Q2147483647' ],
		];
	}

	/**
	 * @dataProvider invalidIdSerializationProvider
	 */
	public function testCannotConstructWithInvalidSerialization( $invalidSerialization ) {
		$this->expectException( InvalidArgumentException::class );
		new ItemId( $invalidSerialization );
	}

	public static function invalidIdSerializationProvider() {
		return [
			[ "Q1\n" ],
			[ 'q' ],
			[ 'p1' ],
			[ 'qq1' ],
			[ '1q' ],
			[ 'q01' ],
			[ 'q 1' ],
			[ ' q1' ],
			[ 'q1 ' ],
			[ '1' ],
			[ ' ' ],
			[ '' ],
			[ '0' ],
			[ 0 ],
			[ 1 ],
			[ 'Q2147483648' ],
			[ 'Q99999999999' ],
			// no longer supported (T291823, T338223)
			[ 'foo:Q42', 'foo:Q42' ],
			[ 'foo:bar:q42', 'foo:bar:Q42' ],
			[ ':Q42', 'Q42' ],
		];
	}

	public function testGetNumericId() {
		$id = new ItemId( 'Q1' );
		$this->assertSame( 1, $id->getNumericId() );
	}

	public function testGetEntityType() {
		$id = new ItemId( 'Q1' );
		$this->assertSame( 'item', $id->getEntityType() );
	}

	public function testSerialize() {
		$id = new ItemId( 'Q1' );
		$this->assertSame( [ 'serialization' => 'Q1' ], $id->__serialize() );
	}

	public function testUnserialize() {
		$id = new ItemId( 'Q1' );
		$id->__unserialize( [ 'serialization' => 'Q2' ] );
		$this->assertSame( 'Q2', $id->getSerialization() );
	}

	public function testUnserializeInvalid(): void {
		$id = new ItemId( 'Q1' );
		$this->expectException( InvalidArgumentException::class );
		$id->__unserialize( [ 'serialization' => 'q' ] );
	}

	public function testUnserializeNotNormalized(): void {
		$id = new ItemId( 'Q1' );
		$this->expectException( InvalidArgumentException::class );
		$id->__unserialize( [ 'serialization' => 'q2' ] );
		// 'q2' is allowed in the constructor (silently uppercased) but not in unserialize()
	}

	/**
	 * @dataProvider numericIdProvider
	 */
	public function testNewFromNumber( $number ) {
		$id = ItemId::newFromNumber( $number );
		$this->assertSame( 'Q' . $number, $id->getSerialization() );
	}

	public static function numericIdProvider() {
		return [
			[ 42 ],
			[ '42' ],
			[ 42.0 ],
			// Check for 32-bit integer overflow on 32-bit PHP systems.
			[ 2147483647 ],
			[ '2147483647' ],
		];
	}

	/**
	 * @dataProvider invalidNumericIdProvider
	 */
	public function testNewFromNumberWithInvalidNumericId( $number ) {
		$this->expectException( InvalidArgumentException::class );
		ItemId::newFromNumber( $number );
	}

	public static function invalidNumericIdProvider() {
		return [
			[ 'Q1' ],
			[ '42.1' ],
			[ 42.1 ],
			[ 2147483648 ],
			[ '2147483648' ],
		];
	}

}
