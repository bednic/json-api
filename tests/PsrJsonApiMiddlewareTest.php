<?php

namespace JSONAPI\Middleware;

use Doctrine\Common\Cache\ArrayCache;
use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Document\Document;
use JSONAPI\Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

function file_get_contents()
{
    return \file_get_contents(__DIR__ . '/resources/request.json');
}

class PsrJsonApiMiddlewareTest extends TestCase implements RequestHandlerInterface
{

    public function testConstruct()
    {
        $mf = new MetadataFactory(
            __DIR__ . '/resources',
            new SimpleCacheAdapter(new ArrayCache())
        );
        $rf = new ResponseFactory();
        $sf = new StreamFactory();
        $middleware = new PsrJsonApiMiddleware($mf, $rf, $sf);
        $this->assertInstanceOf(PsrJsonApiMiddleware::class, $middleware);
        return $middleware;
    }

    /**
     * @depends testConstruct
     * @covers  \JSONAPI\Middleware\PsrJsonApiMiddleware::process
     * @covers  \JSONAPI\Middleware\PsrJsonApiMiddleware::getBody
     */
    public function testProcess(PsrJsonApiMiddleware $middleware)
    {
        $rf = new ServerRequestFactory();
        $request = $rf
            ->createServerRequest('POST', 'http://unit.test.org/getter');
        $response = $middleware->process($request, $this);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(Document::MEDIA_TYPE, $response->getHeader('Content-Type')[0]);
        $this->assertEquals(StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
        $request = $rf->createServerRequest('POST', 'http://unit.test.org/getter')
            ->withAddedHeader('Content-Type', Document::MEDIA_TYPE);
        $response = $middleware->process($request, $this);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(Document::MEDIA_TYPE, $response->getHeader('Content-Type')[0]);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $request = $rf->createServerRequest('GET', 'http://unit.test.org/getter');
        $response = $middleware->process($request, $this);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(Document::MEDIA_TYPE, $response->getHeader('Content-Type')[0]);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->assertInstanceOf(Document::class, $request->getParsedBody());
        $factory = new ResponseFactory();
        return $factory->createResponse();
    }
}
