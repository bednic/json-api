<?php

namespace JSONAPI\Test;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Document\Document;
use JSONAPI\Exception\Document\NotFound;
use JSONAPI\Exception\NotFoundException;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Middleware\PsrJsonApiMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

/**
 * Class PsrJsonApiMiddlewareTest
 *
 * @package JSONAPI\Test
 */
class PsrJsonApiMiddlewareTest extends TestCase
{

    private static $factory;

    public static function setUpBeforeClass(): void
    {
        self::$factory = new MetadataFactory(__DIR__ . '/resources');
    }

    public function testBadHeader()
    {
        $middleware = new PsrJsonApiMiddleware(self::$factory);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeader')->willReturn(['application/json']);
        $request->method('getMethod')->willReturn('POST');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn($response);

        $response = $middleware->process($request, $handler);

        $this->assertEquals(StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
    }

    public function testReturnHeader()
    {
        $middleware = new PsrJsonApiMiddleware(self::$factory);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeader')->willReturn([Document::MEDIA_TYPE]);

        $response = new Response();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn($response);

        $response = $middleware->process($request, $handler);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertTrue(in_array(Document::MEDIA_TYPE, $response->getHeader('Content-Type')));
    }

    public function testHttpException()
    {
        $middleware = new PsrJsonApiMiddleware(self::$factory);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeader')->willReturn([Document::MEDIA_TYPE]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new NotFound());

        $response = $middleware->process($request, $handler);

        $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertTrue(in_array(Document::MEDIA_TYPE, $response->getHeader('Content-Type')));
    }

    public function testJsonBody()
    {
        $middleware = new PsrJsonApiMiddleware(self::$factory);
        $middleware->setStream(__DIR__ . '/resources/request.json');
        $request = ServerRequestFactory::createFromGlobals()
            ->withHeader('Content-Type', Document::MEDIA_TYPE);


        $handler = new class implements RequestHandlerInterface
        {

            /**
             * Handles a request and produces a response.
             *
             * May call other collaborating code to generate the response.
             */
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return (new Response())->withBody(
                    new Stream(fopen(__DIR__ . '/resources/request.json', 'r'))
                );
            }
        };

        $response = $middleware->process($request, $handler);
        $this->assertEquals(
            $response->getBody()->getContents(),
            file_get_contents(__DIR__ . '/resources/request.json')
        );
    }
}
