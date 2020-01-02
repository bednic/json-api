<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:43
 */

namespace JSONAPI\Middleware;

use Exception;
use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Exception\Http\UnsupportedMediaType;
use JSONAPI\Metadata\MetadataFactory;
use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use stdClass;

/**
 * Class PsrJsonApiMiddleware
 *
 * @package JSONAPI\Middleware
 */
class PsrJsonApiMiddleware implements MiddlewareInterface
{
    /**
     * @var MetadataFactory
     */
    private MetadataFactory $metadataFactory;

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;
    /**
     * @var StreamFactoryInterface
     */
    private StreamFactoryInterface $streamFactory;
    /**
     * @var LoggerInterface|null
     */
    private LoggerInterface $logger;


    /**
     * PsrJsonApiMiddleware constructor.
     *
     * @param MetadataFactory          $metadataFactory
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface   $streamFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger = null
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $document = new Document($this->metadataFactory, $request);
            if (
                in_array(
                    $request->getMethod(),
                    [RequestMethodInterface::METHOD_POST, RequestMethodInterface::METHOD_PATCH]
                )
            ) {
                if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
                    throw new UnsupportedMediaType();
                }
                $document->loadRequestData($this->getBody());
            }
            $response = $handler->handle($request->withParsedBody($document));
        } catch (Exception $exception) {
            $document = new Document($this->metadataFactory, $request, $this->logger);
            $error = Error::fromException($exception);
            $document->addError($error);
            $response = $this->responseFactory
                ->createResponse($error->getStatus())
                ->withBody($this->streamFactory->createStream(json_encode($document)));
        }
        return $response->withHeader("Content-Type", Document::MEDIA_TYPE);
    }

    /**
     * @return stdClass
     * @throws JsonException
     */
    private function getBody(): stdClass
    {
        $body = new stdClass();
        if ($data = file_get_contents('php://input')) {
            $body = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
        }
        return $body;
    }
}
