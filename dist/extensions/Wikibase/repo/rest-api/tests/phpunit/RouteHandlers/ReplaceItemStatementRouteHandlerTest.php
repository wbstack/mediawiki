<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\RouteHandlers;

use MediaWiki\Rest\Handler;
use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use Wikibase\Repo\RestApi\RouteHandlers\ReplaceItemStatementRouteHandler;
use Wikibase\Repo\RestApi\UseCases\ErrorResponse;
use Wikibase\Repo\RestApi\UseCases\ReplaceItemStatement\ReplaceItemStatement;

/**
 * @covers \Wikibase\Repo\RestApi\RouteHandlers\ReplaceItemStatementRouteHandler
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 *
 */
class ReplaceItemStatementRouteHandlerTest extends MediaWikiIntegrationTestCase {

	use HandlerTestTrait;

	public function testHandlesUnexpectedErrors(): void {
		$useCase = $this->createStub( ReplaceItemStatement::class );
		$useCase->method( 'execute' )->willThrowException( new \RuntimeException() );
		$this->setService( 'WbRestApi.ReplaceItemStatement', $useCase );

		$routeHandler = $this->newRouteHandlerWithValidRequest();
		$this->validateHandler( $routeHandler );

		$response = $routeHandler->execute();
		$responseBody = json_decode( $response->getBody()->getContents() );
		$this->assertSame( [ 'en' ], $response->getHeader( 'Content-Language' ) );
		$this->assertSame( ErrorResponse::UNEXPECTED_ERROR, $responseBody->code );
	}

	public function testReadWriteAccess(): void {
		$routeHandler = $this->newRouteHandlerWithValidRequest();

		$this->assertTrue( $routeHandler->needsReadAccess() );
		$this->assertTrue( $routeHandler->needsWriteAccess() );
	}

	private function newRouteHandlerWithValidRequest(): Handler {
		$routeHandler = ReplaceItemStatementRouteHandler::factory();
		$this->initHandler(
			$routeHandler,
			new RequestData( [
					'method' => 'PUT',
					'headers' => [ 'Content-Type' => 'application/json' ],
					'pathParams' => [
						ReplaceItemStatementRouteHandler::ITEM_ID_PATH_PARAM => 'Q1',
						ReplaceItemStatementRouteHandler::STATEMENT_ID_PATH_PARAM => 'Q1$1e63e3d9-4bd4-7671-706c-b745db23c3f1',
					],
					'bodyContents' => json_encode( [
						'statement' => [
							'type' => 'statement',
							'mainsnak' => [
								'snaktype' => 'novalue',
								'property' => 'P1',
							],
						],
					] )
				]
			)
		);
		return $routeHandler;
	}
}
