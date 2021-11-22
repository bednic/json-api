<?php

declare(strict_types=1);

namespace JSONAPI\Middleware;

use JSONAPI\Configuration;
use JSONAPI\Document\Document;
use JSONAPI\Document\Error\DefaultErrorFactory;
use JSONAPI\Document\Error\ErrorFactory;
use JSONAPI\Exception\Http\UnsupportedMediaType;
use JSONAPI\Document\BuilderFactory;
use JSONAPI\URI\URIParser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Swaggest\JsonSchema\Exception;
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
    public const PARSED_URI = '__jsonapi:parsed-uri__';
    public const BUILDER = '__jsonapi:builder__';
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
     * @var SchemaContract
     */
    private SchemaContract $output;
    /**
     * @var ErrorFactory
     */
    private ErrorFactory $errorFactory;
    /**
     * @var Configuration configuration
     */
    private Configuration $configuration;

    /**
     * PsrJsonApiMiddleware constructor.
     *
     * @param Configuration            $configuration
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface   $streamFactory
     * @param ErrorFactory|null $errorFactory
     *
     * @throws Exception
     * @throws InvalidValue
     */
    public function __construct(
        Configuration $configuration,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ErrorFactory $errorFactory = null
    ) {
        $this->configuration   = $configuration;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->logger          = $configuration->getLogger();
        $this->errorFactory    = $errorFactory ?? new DefaultErrorFactory();
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
            $parsedUri = (new URIParser($this->configuration))->parse($request);
            $docBuilder = (new BuilderFactory($this->configuration))->create($request);

            $request = $request
                ->withAttribute(self::PARSED_URI, $parsedUri)
                ->withAttribute(self::BUILDER, $docBuilder);

            $request->getBody()->rewind();
            $content = $request->getBody()->getContents();
            if (strlen($content) > 0) {
                if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
                    throw new UnsupportedMediaType();
                }
                $documentParser = new DocumentParser(
                    $this->configuration->getMetadataRepository(),
                    $parsedUri->getPath()
                );
                $data           = $request->getBody()->getContents();
                $document       = $documentParser->decode($data);
                $request        = $request->withParsedBody($document);
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
