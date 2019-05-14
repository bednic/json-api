<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:43
 */

namespace JSONAPI\Middleware;

use JSONAPI\Document\Document;
use JSONAPI\Exception\HttpException;
use JSONAPI\Exception\UnsupportedMediaTypeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Class PsrJsonApiMiddleware
 *
 * @package JSONAPI\Middleware
 */
class PsrJsonApiMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $responseFactory = new ResponseFactory();
        try {
            if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
                throw new UnsupportedMediaTypeException();
            }
            /** @var ResponseInterface $response */
            $response = $handler->handle($request);
        } catch (HttpException $exception) {
            $response = $responseFactory->createResponse($exception->getStatus());
        }
        return $response->withHeader("Content-Type", Document::MEDIA_TYPE);
    }
}
