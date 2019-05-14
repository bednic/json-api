<?php

namespace Test\JSONAPI;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Document\Document;
use JSONAPI\Exception\NotFoundException;
use JSONAPI\Middleware\PsrJsonApiMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class PsrJsonApiMiddlewareTest extends TestCase
{

    public function testBadHeader()
    {

        $middleware = new PsrJsonApiMiddleware();
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeader')->willReturn(['application/json']);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn($response);
        $response = $middleware->process($request, $handler);
        $this->assertEquals(StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
    }

    public function testReturnHeader()
    {
        $middleware = new PsrJsonApiMiddleware();

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
        $middleware = new PsrJsonApiMiddleware();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getHeader')->willReturn([Document::MEDIA_TYPE]);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willThrowException(new NotFoundException());
        $response = $middleware->process($request, $handler);
        $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertTrue(in_array(Document::MEDIA_TYPE, $response->getHeader('Content-Type')));
    }
}