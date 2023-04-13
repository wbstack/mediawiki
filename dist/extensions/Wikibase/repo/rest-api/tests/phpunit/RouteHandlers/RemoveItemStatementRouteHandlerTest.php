<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\RouteHandlers;

use MediaWiki\Rest\Handler;
use MediaWiki\Rest\RequestData;
use MediaWiki\Tests\Rest\Handler\HandlerTestTrait;
use MediaWikiIntegrationTestCase;
use Wikibase\Repo\RestApi\RouteHandlers\RemoveItemStatementRouteHandler;
use Wikibase\Repo\RestApi\UseCases\ErrorResponse;
use Wikibase\Repo\RestApi\UseCases\RemoveItemStatement\RemoveItemStatement;

/**
 * @covers \Wikibase\Repo\RestApi\RouteHandlers\RemoveItemStatementRouteHandler
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class RemoveItemStatementRouteHandlerTest extends MediaWikiIntegrationTestCase {

	use HandlerTestTrait;

	public function testHandlesUnexpectedErrors(): void {
		$useCase = $this->createStub( RemoveItemStatement::class );
		$useCase->method( 'execute' )->willThrowException( new \RuntimeException() );
		$this->setService( 'WbRestApi.RemoveItemStatement', $useCase );

		$routeHandler = $this->newHandlerWithValidRequest();
		$this->validateHandler( $routeHandler );

		$response = $routeHandler->execute();
		$responseBody = json_decode( $response->getBody()->getContents() );
		$this->assertSame( [ 'en' ], $response->getHeader( 'Content-Language' ) );
		$this->assertSame( ErrorResponse::UNEXPECTED_ERROR, $responseBody->code );
	}

	public function testReadWriteAccess(): void {
		$routeHandler = $this->newHandlerWithValidRequest();

		$this->assertTrue( $routeHandler->needsReadAccess() );
		$this->assertTrue( $routeHandler->needsWriteAccess() );
	}

	private function newHandlerWithValidRequest(): Handler {
		$routeHandler = RemoveItemStatementRouteHandler::factory();
		$this->initHandler(
			$routeHandler,
			new RequestData( [
					'method' => 'DELETE',
					'headers' => [ 'Content-Type' => 'application/json' ],
					'pathParams' => [
						RemoveItemStatementRouteHandler::ITEM_ID_PATH_PARAM => 'Q123',
						RemoveItemStatementRouteHandler::STATEMENT_ID_PATH_PARAM => 'Q123$some-guid'
					],
					'bodyContents' => json_encode( [
						'tags' => [ 'edit', 'tags' ],
						'bot' => true,
					] )
				]
			)
		);
		return $routeHandler;
	}
}
