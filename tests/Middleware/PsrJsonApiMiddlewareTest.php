<?php

namespace JSONAPI\Test\Middleware {

    use Doctrine\Common\Cache\ArrayCache;
    use Fig\Http\Message\RequestMethodInterface;
    use Fig\Http\Message\StatusCodeInterface;
    use JSONAPI\Document\Document;
    use JSONAPI\Driver\AnnotationDriver;
    use JSONAPI\Metadata\MetadataFactory;
    use JSONAPI\Middleware\PsrJsonApiMiddleware;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
    use Slim\Psr7\Factory\ResponseFactory;
    use Slim\Psr7\Factory\ServerRequestFactory;
    use Slim\Psr7\Factory\StreamFactory;

    class PsrJsonApiMiddlewareTest extends TestCase implements RequestHandlerInterface
    {

        public function testConstruct()
        {
            $mf = MetadataFactory::create(
                RESOURCES . '/valid',
                new SimpleCacheAdapter(new ArrayCache()),
                new AnnotationDriver()
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
         * @covers  \JSONAPI\Middleware\PsrJsonApiMiddleware::loadRequestData
         * @covers  \JSONAPI\Middleware\PsrJsonApiMiddleware::jsonToResourceObject
         */
        public function testProcess(PsrJsonApiMiddleware $middleware)
        {
            $rf = new ServerRequestFactory();
            $request = $rf
                ->createServerRequest(RequestMethodInterface::METHOD_POST, 'http://unit.test.org/getter');
            $response = $middleware->process($request, $this);
            $this->assertInstanceOf(ResponseInterface::class, $response);
            $this->assertEquals(Document::MEDIA_TYPE, $response->getHeader('Content-Type')[0]);
            $this->assertEquals(StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());

            $request = $rf->createServerRequest(RequestMethodInterface::METHOD_POST, 'http://unit.test.org/getter')
                ->withAddedHeader('Content-Type', Document::MEDIA_TYPE);
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
                in_array($request->getMethod(), [
                RequestMethodInterface::METHOD_POST,
                RequestMethodInterface::METHOD_PATCH,
                RequestMethodInterface::METHOD_DELETE
                ])
            ) {
                $this->assertInstanceOf(Document::class, $request->getParsedBody());
            }
            $factory = new ResponseFactory();
            return $factory->createResponse();
        }
    }
}
namespace JSONAPI\Middleware {
    function file_get_contents()
    {
        return \file_get_contents(RESOURCES . '/request.json');
    }
}
