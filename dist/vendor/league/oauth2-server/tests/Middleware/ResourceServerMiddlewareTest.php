<?php

namespace LeagueTests\Middleware;

use DateInterval;
use DateTimeImmutable;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Middleware\ResourceServerMiddleware;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use LeagueTests\Stubs\AccessTokenEntity;
use LeagueTests\Stubs\ClientEntity;
use PHPUnit\Framework\TestCase;

class ResourceServerMiddlewareTest extends TestCase
{
    public function testValidResponse()
    {
        $server = new ResourceServer(
            $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock(),
            'file://' . __DIR__ . '/../Stubs/public.key'
        );

        $client = new ClientEntity();
        $client->setIdentifier('clientName');

        $accessToken = new AccessTokenEntity();
        $accessToken->setIdentifier('test');
        $accessToken->setUserIdentifier(123);
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->add(new DateInterval('PT1H')));
        $accessToken->setClient($client);
        $accessToken->setPrivateKey(new CryptKey('file://' . __DIR__ . '/../Stubs/private.key'));

        $token = (string) $accessToken;

        $request = (new ServerRequest())->withHeader('authorization', \sprintf('Bearer %s', $token));

        $middleware = new ResourceServerMiddleware($server);
        $response = $middleware->__invoke(
            $request,
            new Response(),
            function () {
                $this->assertEquals('test', \func_get_args()[0]->getAttribute('oauth_access_token_id'));

                return \func_get_args()[1];
            }
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testValidResponseExpiredToken()
    {
        $server = new ResourceServer(
            $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock(),
            'file://' . __DIR__ . '/../Stubs/public.key'
        );

        $client = new ClientEntity();
        $client->setIdentifier('clientName');

        $accessToken = new AccessTokenEntity();
        $accessToken->setIdentifier('test');
        $accessToken->setUserIdentifier(123);
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->sub(new DateInterval('PT1H')));
        $accessToken->setClient($client);
        $accessToken->setPrivateKey(new CryptKey('file://' . __DIR__ . '/../Stubs/private.key'));

        $token = (string) $accessToken;

        $request = (new ServerRequest())->withHeader('authorization', \sprintf('Bearer %s', $token));

        $middleware = new ResourceServerMiddleware($server);
        $response = $middleware->__invoke(
            $request,
            new Response(),
            function () {
                $this->assertEquals('test', \func_get_args()[0]->getAttribute('oauth_access_token_id'));

                return \func_get_args()[1];
            }
        );

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testErrorResponse()
    {
        $server = new ResourceServer(
            $this->getMockBuilder(AccessTokenRepositoryInterface::class)->getMock(),
            'file://' . __DIR__ . '/../Stubs/public.key'
        );

        $request = (new ServerRequest())->withHeader('authorization', '');

        $middleware = new ResourceServerMiddleware($server);
        $response = $middleware->__invoke(
            $request,
            new Response(),
            function () {
                return \func_get_args()[1];
            }
        );

        $this->assertEquals(401, $response->getStatusCode());
    }
}
