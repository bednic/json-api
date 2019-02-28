<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 14.02.2019
 * Time: 13:44
 */

namespace JSONAPI\Middleware;


use JSONAPI\Document\Document;
use JSONAPI\Exception\UnsupportedMediaType;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SlimJsonApiMiddleware
 * Implementation for SlimPHP framework
 * @package JSONAPI\Middleware
 */
class SlimJsonApiMiddleware
{

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $next
     * @return ResponseInterface
     * @throws UnsupportedMediaType
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
            throw new UnsupportedMediaType();
        }
        /** @var ResponseInterface $response */
        $response = $next($request, $response);
        $response->withHeader("Content-Type", Document::MEDIA_TYPE);
        return $response;
    }
}
