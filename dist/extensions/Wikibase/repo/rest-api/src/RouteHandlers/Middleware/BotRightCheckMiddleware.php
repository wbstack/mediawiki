<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\RouteHandlers\Middleware;

use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Rest\Handler;
use MediaWiki\Rest\Response;
use Wikibase\Repo\RestApi\Application\UseCases\UseCaseError;
use Wikibase\Repo\RestApi\RouteHandlers\ResponseFactory;

/**
 * This middleware errors for bot edit requests given the user doesn't have the 'bot' right.
 *
 * @license GPL-2.0-or-later
 */
class BotRightCheckMiddleware implements Middleware {

	private PermissionManager $permissionManager;
	private ResponseFactory $responseFactory;

	public function __construct( PermissionManager $permissionManager, ResponseFactory $responseFactory ) {
		$this->permissionManager = $permissionManager;
		$this->responseFactory = $responseFactory;
	}

	public function run( Handler $routeHandler, callable $runNext ): Response {
		$jsonBody = $routeHandler->getValidatedBody();
		$user = $routeHandler->getAuthority()->getUser();

		if ( isset( $jsonBody['bot'] ) && $jsonBody['bot'] && !$this->permissionManager->userHasRight( $user, 'bot' ) ) {
			return $this->responseFactory->newErrorResponseFromException(
				UseCaseError::newPermissionDenied( UseCaseError::PERMISSION_DENIED_REASON_UNAUTHORIZED_BOT_EDIT )
			);
		}

		return $runNext();
	}

}
