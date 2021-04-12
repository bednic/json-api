<?php

declare(strict_types=1);

namespace JSONAPI\Test\Middleware;

use Doctrine\Common\Cache\ArrayCache;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Document\Document;
use JSONAPI\Driver\AnnotationDriver;
use JSONAPI\Factory\MetadataFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Middleware\PsrJsonApiMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Stream;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Class PsrJsonApiMiddlewareTest
 *
 * @package JSONAPI\Test\Middleware
 */
class PsrJsonApiMiddlewareTest extends TestCase implements RequestHandlerInterface
{

    private static MetadataRepository $mf;
    private static $baseURL = 'http://unit.test.org/';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        self::$mf = MetadataFactory::create(
            [RESOURCES . '/valid'],
            new Psr16Cache(new ArrayAdapter()),
            new AnnotationDriver()
        );
    }

    public function testConstruct()
    {
        $rf = new ResponseFactory();
        $sf = new StreamFactory();
        $middleware = new PsrJsonApiMiddleware(self::$mf, self::$baseURL, $rf, $sf);
        $this->assertInstanceOf(PsrJsonApiMiddleware::class, $middleware);
        return $middleware;
    }

    /**
     * @depends testConstruct
     * @covers  \JSONAPI\Middleware\PsrJsonApiMiddleware::process
     */
    public function testProcess(PsrJsonApiMiddleware $middleware)
    {
        $rf = new ServerRequestFactory();
        $request = $rf
            ->createServerRequest(RequestMethodInterface::METHOD_POST, 'http://unit.test.org/getter')
            ->withBody(
                (new Stream(fopen(RESOURCES . DIRECTORY_SEPARATOR . 'request.json', 'r')))
            );
        $response = $middleware->process($request, $this);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(Document::MEDIA_TYPE, $response->getHeader('Content-Type')[0]);
        $this->assertEquals(StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());

        $request = $rf->createServerRequest(RequestMethodInterface::METHOD_POST, 'http://unit.test.org/getter')
            ->withAddedHeader('Content-Type', Document::MEDIA_TYPE)
            ->withBody(
                (new Stream(fopen(RESOURCES . DIRECTORY_SEPARATOR . 'request.json', 'r')))
            );
        $response = $middleware->process($request, $this);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(Document::MEDIA_TYPE, $response->getHeader('Content-Type')[0]);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $request = $rf->createServerRequest(RequestMethodInterface::METHOD_GET, 'http://unit.test.org/getter');
        $response = $middleware->process($request, $this);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(Document::MEDIA_TYPE, $response->getHeader('Content-Type')[0]);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (
            in_array(
                $request->getMethod(),
                [
                RequestMethodInterface::METHOD_POST,
                RequestMethodInterface::METHOD_PATCH,
                RequestMethodInterface::METHOD_DELETE
                ]
            )
        ) {
            $this->assertInstanceOf(Document::class, $request->getParsedBody());
        }
        $factory = new ResponseFactory();
        return $factory->createResponse()->withBody(
            new Stream(fopen(RESOURCES . DIRECTORY_SEPARATOR . 'response.json', 'r'))
        );
    }
}
