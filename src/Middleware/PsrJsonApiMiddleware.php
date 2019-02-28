<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:43
 */

namespace JSONAPI\Middleware;


use JSONAPI\Document\Document;
use JSONAPI\Exception\UnsupportedMediaType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PsrJsonApiMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws UnsupportedMediaType
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
            throw new UnsupportedMediaType();
        }
        /** @var ResponseInterface $response */
        $response = $handler->handle($request);
        return $response->withHeader("Content-Type", Document::MEDIA_TYPE);

    }
}
