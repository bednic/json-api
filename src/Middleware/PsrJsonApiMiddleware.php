<?php

declare(strict_types=1);

namespace JSONAPI\Middleware;

use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Document\Document;
use JSONAPI\Document\Error\ErrorFactory;
use JSONAPI\Exception\Http\UnsupportedMediaType;
use JSONAPI\Factory\DocumentErrorFactory;
use JSONAPI\Factory\DocumentFactory;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Path\PathParser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swaggest\JsonSchema\Exception as SchemaException;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;
use Throwable;

/**
 * Class PsrJsonApiMiddleware
 *
 * @package JSONAPI\Middleware
 */
class PsrJsonApiMiddleware implements MiddlewareInterface
{
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $repository;
    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;
    /**
     * @var StreamFactoryInterface
     */
    private StreamFactoryInterface $streamFactory;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var string
     */
    private string $baseURL;
    /**
     * @var SchemaContract
     */
    private SchemaContract $input;
    /**
     * @var SchemaContract
     */
    private SchemaContract $output;
    /**
     * @var ErrorFactory
     */
    private ErrorFactory $errorFactory;


    /**
     * PsrJsonApiMiddleware constructor.
     *
     * @param MetadataRepository       $repository
     * @param string                   $baseURL
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface   $streamFactory
     * @param LoggerInterface|null     $logger
     * @param ErrorFactory|null        $errorFactory
     *
     * @throws InvalidValue
     * @throws SchemaException
     */
    public function __construct(
        MetadataRepository $repository,
        string $baseURL,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger = null,
        ErrorFactory $errorFactory = null
    ) {
        $this->repository      = $repository;
        $this->baseURL         = $baseURL;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->logger          = $logger ?? new NullLogger();
        $this->errorFactory    = $errorFactory ?? new DocumentErrorFactory();
        $this->input           = Schema::import(
            json_decode(file_get_contents(__DIR__ . '/in.json'))
        );
        $this->output          = Schema::import(
            json_decode(file_get_contents(__DIR__ . '/out.json'))
        );
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
                if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
                    throw new UnsupportedMediaType();
                }
                $path = (new PathParser($this->repository, $this->baseURL, $request->getMethod()))
                    ->parse($request->getUri()->getPath());
                if ($request->getMethod() !== RequestMethodInterface::METHOD_DELETE || $path->isRelationship()) {
                    $documentBuilder = new DocumentFactory($this->repository, $path);
                    $request->getBody()->rewind();
                    $data     = $request->getBody()->getContents();
                    $document = $documentBuilder->decode($data);
                    $request  = $request->withParsedBody($document);
                }
            }
            $response = $handler->handle($request);
            $content  = $response->getBody()->getContents();
            $response->getBody()->rewind();
            if (strlen($content) > 0) {
                $this->output->in(
                    json_decode($content, false, 512, JSON_THROW_ON_ERROR)
                );
            }
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
            $document = new Document();
            $error    = $this->errorFactory->fromThrowable($exception);
            $document->addError($error);
            $response = $this->responseFactory
                ->createResponse($error->getStatus())
                ->withBody($this->streamFactory->createStream(json_encode($document, JSON_PRESERVE_ZERO_FRACTION)));
        }
        return $response->withHeader("Content-Type", Document::MEDIA_TYPE);
    }
}
