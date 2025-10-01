<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\RouteHandlers\Middleware;

use Generator;
use MediaWiki\Rest\Handler;
use MediaWiki\Rest\Reporter\ErrorReporter;
use MediaWiki\Rest\Response;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use TypeError;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\UnexpectedErrorHandlerMiddleware;
use Wikibase\Repo\RestApi\RouteHandlers\ResponseFactory;

/**
 * @covers \Wikibase\Repo\RestApi\RouteHandlers\Middleware\UnexpectedErrorHandlerMiddleware
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class UnexpectedErrorHandlerMiddlewareTest extends TestCase {

	private ErrorReporter $errorReporter;

	protected function setUp(): void {
		parent::setUp();

		$this->errorReporter = $this->createStub( ErrorReporter::class );
	}

	/**
	 * @dataProvider throwableProvider
	 */
	public function testHandlesError( Throwable $throwable ): void {
		$response = $this->newMiddleware()->run(
			$this->createStub( Handler::class ),
			function () use ( $throwable ): Response {
				throw $throwable;
			}
		);

		$this->assertSame( [ 'en' ], $response->getHeader( 'Content-Language' ) );
		$responseBody = json_decode( $response->getBody()->getContents() );
		$this->assertSame(
			UseCaseError::UNEXPECTED_ERROR,
			$responseBody->code
		);
	}

	public function testGivenNoError_returnsRouteResponse(): void {
		$expectedResponse = $this->createStub( Response::class );

		$response = $this->newMiddleware()->run(
			$this->createStub( Handler::class ),
			fn() => $expectedResponse
		);

		$this->assertSame( $expectedResponse, $response );
	}

	public function testReportsError(): void {
		$routeHandler = $this->createStub( Handler::class );
		$exception = new RuntimeException();
		$this->errorReporter = $this->createMock( ErrorReporter::class );
		$this->errorReporter->expects( $this->once() )
			->method( 'reportError' )
			->with(
				$exception,
				$routeHandler,
				$this->anything()
			);

		$this->newMiddleware()->run(
			$routeHandler,
			function () use ( $exception ): void {
				throw $exception;
			}
		);
	}

	public static function throwableProvider(): Generator {
		yield [ new TypeError() ];
		yield [ new RuntimeException() ];
	}

	private function newMiddleware(): UnexpectedErrorHandlerMiddleware {
		return new UnexpectedErrorHandlerMiddleware(
			new ResponseFactory(),
			$this->errorReporter
		);
	}

}
