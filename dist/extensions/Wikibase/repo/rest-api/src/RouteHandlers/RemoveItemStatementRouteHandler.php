<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\RouteHandlers;

use MediaWiki\HookContainer\HookRunner;
use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\Handler;
use MediaWiki\Rest\RequestInterface;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\ResponseInterface;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\StringStream;
use MediaWiki\Rest\Validator\Validator;
use Wikibase\Repo\RestApi\Application\UseCases\ItemRedirect;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemStatement\RemoveItemStatement;
use Wikibase\Repo\RestApi\Application\UseCases\RemoveItemStatement\RemoveItemStatementRequest;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\AuthenticationMiddleware;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\BotRightCheckMiddleware;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\MiddlewareHandler;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\TempUserCreationResponseHeaderMiddleware;
use Wikibase\Repo\RestApi\RouteHandlers\Middleware\UserAgentCheckMiddleware;
use Wikibase\Repo\RestApi\WbRestApi;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * @license GPL-2.0-or-later
 */
class RemoveItemStatementRouteHandler extends SimpleHandler {
	use AssertValidTopLevelFields;

	private const ITEM_ID_PATH_PARAM = 'item_id';
	private const STATEMENT_ID_PATH_PARAM = 'statement_id';
	private const TAGS_BODY_PARAM = 'tags';
	private const BOT_BODY_PARAM = 'bot';
	private const COMMENT_BODY_PARAM = 'comment';

	private const TAGS_PARAM_DEFAULT = [];
	private const BOT_PARAM_DEFAULT = false;
	private const COMMENT_PARAM_DEFAULT = null;

	private RemoveItemStatement $useCase;
	private ResponseFactory $responseFactory;
	private MiddlewareHandler $middlewareHandler;

	public function __construct(
		RemoveItemStatement $useCase,
		ResponseFactory $responseFactory,
		MiddlewareHandler $middlewareHandler
	) {
		$this->useCase = $useCase;
		$this->responseFactory = $responseFactory;
		$this->middlewareHandler = $middlewareHandler;
	}

	public static function factory(): Handler {
		$responseFactory = new ResponseFactory();
		return new self(
			WbRestApi::getRemoveItemStatement(),
			new ResponseFactory(),
			new MiddlewareHandler( [
				WbRestApi::getUnexpectedErrorHandlerMiddleware(),
				new UserAgentCheckMiddleware(),
				new AuthenticationMiddleware( MediaWikiServices::getInstance()->getUserIdentityUtils() ),
				new BotRightCheckMiddleware( MediaWikiServices::getInstance()->getPermissionManager(), $responseFactory ),
				WbRestApi::getPreconditionMiddlewareFactory()->newPreconditionMiddleware(
					fn( RequestInterface $request ): string => $request->getPathParam( self::ITEM_ID_PATH_PARAM )
				),
				WbRestApi::getStatementRedirectMiddlewareFactory()->newStatementRedirectMiddleware(
					self::STATEMENT_ID_PATH_PARAM,
					self::ITEM_ID_PATH_PARAM
				),
				new TempUserCreationResponseHeaderMiddleware( new HookRunner( MediaWikiServices::getInstance()->getHookContainer() ) ),
			] )
		);
	}

	/**
	 * Preconditions are checked via {@link PreconditionMiddleware}
	 */
	public function checkPreconditions(): ?ResponseInterface {
		return null;
	}

	/**
	 * @param mixed ...$args
	 */
	public function run( ...$args ): Response {
		return $this->middlewareHandler->run( $this, [ $this, 'runUseCase' ], $args );
	}

	public function runUseCase( string $itemId, string $statementId ): Response {
		$requestBody = $this->getValidatedBody();

		try {
			$this->useCase->execute(
				new RemoveItemStatementRequest(
					$itemId,
					$statementId,
					$requestBody[self::TAGS_BODY_PARAM] ?? self::TAGS_PARAM_DEFAULT,
					$requestBody[self::BOT_BODY_PARAM] ?? self::BOT_PARAM_DEFAULT,
					$requestBody[self::COMMENT_BODY_PARAM] ?? self::COMMENT_PARAM_DEFAULT,
					$this->getUsername()
				)
			);
		} catch ( UseCaseError $exception ) {
			return $this->responseFactory->newErrorResponseFromException( $exception );
		} catch ( ItemRedirect $e ) {
			return $this->responseFactory
				->newErrorResponseFromException( UseCaseError::newResourceNotFound( 'statement' ) );
		}

		return $this->newSuccessHttpResponse();
	}

	public function validate( Validator $restValidator ): void {
		$this->assertValidTopLevelTypes( $this->getRequest()->getParsedBody(), $this->getBodyParamSettings() );
		parent::validate( $restValidator );
	}

	public function getParamSettings(): array {
		return [
			self::ITEM_ID_PATH_PARAM => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			self::STATEMENT_ID_PATH_PARAM => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	public function getBodyParamSettings(): array {
		return [
			self::TAGS_BODY_PARAM => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => self::TAGS_PARAM_DEFAULT,
			],
			self::BOT_BODY_PARAM => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => self::BOT_PARAM_DEFAULT,
			],
			self::COMMENT_BODY_PARAM => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => self::COMMENT_PARAM_DEFAULT,
			],
		];
	}

	private function newSuccessHttpResponse(): Response {
		$httpResponse = $this->getResponseFactory()->create();
		$httpResponse->setStatus( 200 );
		$httpResponse->setHeader( 'Content-Type', 'application/json' );
		$httpResponse->setHeader( 'Content-Language', 'en' );
		$httpResponse->setBody( new StringStream( '"Statement deleted"' ) );

		return $httpResponse;
	}

	private function getUsername(): ?string {
		$mwUser = $this->getAuthority()->getUser();
		return $mwUser->isRegistered() ? $mwUser->getName() : null;
	}

}
