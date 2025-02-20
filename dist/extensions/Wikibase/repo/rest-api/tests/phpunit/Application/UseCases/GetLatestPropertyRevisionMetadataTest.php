<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\Application\UseCases;

use PHPUnit\Framework\TestCase;
use Wikibase\DataModel\Entity\NumericPropertyId;
use Wikibase\Repo\RestApi\Application\UseCases\GetLatestPropertyRevisionMetadata;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\Domain\ReadModel\LatestPropertyRevisionMetadataResult;
use Wikibase\Repo\RestApi\Domain\Services\PropertyRevisionMetadataRetriever;

/**
 * @covers \Wikibase\Repo\RestApi\Application\UseCases\GetLatestPropertyRevisionMetadata
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class GetLatestPropertyRevisionMetadataTest extends TestCase {

	public function testExecute(): void {
		$propertyId = new NumericPropertyId( 'P321' );
		$expectedRevisionId = 123;
		$expectedLastModified = '20220101001122';

		$metadataRetriever = $this->createStub( PropertyRevisionMetadataRetriever::class );
		$metadataRetriever->method( 'getLatestRevisionMetadata' )
			->willReturn( LatestPropertyRevisionMetadataResult::concreteRevision( $expectedRevisionId, $expectedLastModified ) );

		[ $revId, $lastModified ] = $this->newGetRevisionMetadata( $metadataRetriever )->execute( $propertyId );

		$this->assertSame( $expectedRevisionId, $revId );
		$this->assertSame( $expectedLastModified, $lastModified );
	}

	public function testGivenPropertyDoesNotExist_throwsUseCaseError(): void {
		$propertyId = new NumericPropertyId( 'P321' );

		$metadataRetriever = $this->createStub( PropertyRevisionMetadataRetriever::class );
		$metadataRetriever->method( 'getLatestRevisionMetadata' )
			->willReturn( LatestPropertyRevisionMetadataResult::propertyNotFound() );

		try {
			$this->newGetRevisionMetadata( $metadataRetriever )->execute( $propertyId );
			$this->fail( 'this should not be reached' );
		} catch ( UseCaseError $e ) {
			$this->assertSame( UseCaseError::RESOURCE_NOT_FOUND, $e->getErrorCode() );
			$this->assertSame( 'The requested resource does not exist', $e->getErrorMessage() );
			$this->assertSame( [ 'resource_type' => 'property' ], $e->getErrorContext() );
		}
	}

	private function newGetRevisionMetadata( PropertyRevisionMetadataRetriever $metadataRetriever ): GetLatestPropertyRevisionMetadata {
		return new GetLatestPropertyRevisionMetadata( $metadataRetriever );
	}

}
