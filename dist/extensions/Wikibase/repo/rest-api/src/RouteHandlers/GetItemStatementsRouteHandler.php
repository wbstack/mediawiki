<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\RouteHandlers;

use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\Handler;
use MediaWiki\Rest\RequestInterface;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\ResponseInterface;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\StringStream;
use Wikibase\Repo\RestApi\Application\Serialization\StatementListSerializer;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemStatements\GetItemStatements;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemStatements\GetItemStatementsRequest;
use Wikibase\Repo\RestApi\Application\UseCases\GetItemStatements\GetItemStatementsResponse;
use Wikibase\Repo\RestApi\Application\UseCases\ItemRedirect;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\AuthenticationMiddleware;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\MiddlewareHandler;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\UserAgentCheckMiddleware;
use Wikibase\Repo\RestApi\WbRestApi;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * @license GPL-2.0-or-later
 */
class GetItemStatementsRouteHandler extends SimpleHandler {

	private const ITEM_ID_PATH_PARAM = 'item_id';
	private const PROPERTY_QUERY_PARAM = 'property';

	private GetItemStatements $getItemStatements;

	private StatementListSerializer $statementListSerializer;

	private ResponseFactory $responseFactory;

	private MiddlewareHandler $middlewareHandler;

	public function __construct(
		GetItemStatements $getItemStatements,
		StatementListSerializer $statementListSerializer,
		ResponseFactory $responseFactory,
		MiddlewareHandler $middlewareHandler
	) {
		$this->getItemStatements = $getItemStatements;
		$this->statementListSerializer = $statementListSerializer;
		$this->responseFactory = $responseFactory;
		$this->middlewareHandler = $middlewareHandler;
	}

	public static function factory(): Handler {
		$responseFactory = new ResponseFactory();
		return new self(
			WbRestApi::getGetItemStatements(),
			new StatementListSerializer( WbRestApi::getStatementSerializer() ),
			$responseFactory,
			new MiddlewareHandler( [
				WbRestApi::getUnexpectedErrorHandlerMiddleware(),
				new UserAgentCheckMiddleware(),
				new AuthenticationMiddleware( MediaWikiServices::getInstance()->getUserIdentityUtils() ),
				WbRestApi::getPreconditionMiddlewareFactory()->newPreconditionMiddleware(
					fn( RequestInterface $request ): string => $request->getPathParam( self::ITEM_ID_PATH_PARAM )
				),
			] )
		);
	}

	/**
	 * @param mixed ...$args
	 */
	public function run( ...$args ): Response {
		return $this->middlewareHandler->run( $this, [ $this, 'runUseCase' ], $args );
	}

	public function runUseCase( string $itemId ): Response {
		$statementPropertyId = $this->getRequest()->getQueryParams()[self::PROPERTY_QUERY_PARAM] ?? null;
		try {
			return $this->newSuccessHttpResponse(
				$this->getItemStatements->execute(
					new GetItemStatementsRequest( $itemId, $statementPropertyId )
				)
			);
		} catch ( ItemRedirect $e ) {
			return $this->newRedirectHttpResponse( $e );
		} catch ( UseCaseError $e ) {
			return $this->responseFactory->newErrorResponseFromException( $e );
		}
	}

	private function newSuccessHttpResponse( GetItemStatementsResponse $useCaseResponse ): Response {
		$httpResponse = $this->getResponseFactory()->create();
		$httpResponse->setHeader( 'Content-Type', 'application/json' );
		$httpResponse->setHeader( 'Last-Modified', wfTimestamp( TS_RFC2822, $useCaseResponse->getLastModified() ) );
		$this->setEtagFromRevId( $httpResponse, $useCaseResponse->getRevisionId() );
		$httpResponse->setBody(
			new StringStream(
				json_encode( $this->statementListSerializer->serialize( $useCaseResponse->getStatements() ) )
			)
		);

		return $httpResponse;
	}

	private function newRedirectHttpResponse( ItemRedirect $e ): Response {
		$httpResponse = $this->getResponseFactory()->create();
		$httpResponse->setHeader(
			'Location',
			$this->getRouteUrl( [ self::ITEM_ID_PATH_PARAM => $e->getRedirectTargetId() ] )
		);
		$httpResponse->setStatus( 308 );

		return $httpResponse;
	}

	private function setEtagFromRevId( Response $response, int $revId ): void {
		$response->setHeader( 'ETag', "\"$revId\"" );
	}

	public function getParamSettings(): array {
		return [
			self::ITEM_ID_PATH_PARAM => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			self::PROPERTY_QUERY_PARAM => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}

	public function needsWriteAccess(): bool {
		return false;
	}

	/**
	 * Preconditions are checked via {@link PreconditionMiddleware}
	 */
	public function checkPreconditions(): ?ResponseInterface {
		return null;
	}

}
