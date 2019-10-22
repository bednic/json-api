<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:43
 */

namespace JSONAPI\Middleware;

use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Exception\Document\BadRequest;
use JSONAPI\Exception\Document\UnsupportedMediaType;
use JSONAPI\Metadata\MetadataFactory;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

/**
 * Class PsrJsonApiMiddleware
 *
 * @package JSONAPI\Middleware
 */
class PsrJsonApiMiddleware implements MiddlewareInterface
{

    /**
     * @var Schema
     */
    private static $schema;
    /**
     * @var Validator
     */
    private static $validator;
    /**
     * @var MetadataFactory
     */
    private $factory;
    /**
     * @var LoggerInterface|null
     */
    private $logger;
    /**
     * @var string
     */
    private $streamPath = 'php://input';

    /**
     * PsrJsonApiMiddleware constructor.
     *
     * @param MetadataFactory $factory
     * @param LoggerInterface $logger
     */
    public function __construct(MetadataFactory $factory, LoggerInterface $logger = null)
    {
        $this->factory = $factory;
        $this->logger = $logger ?? new NullLogger();
        self::$schema = Schema::fromJsonString(file_get_contents(__DIR__ . '/../../schema.json'));
        self::$validator = new Validator();
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
                    [RequestMethodInterface::METHOD_POST, RequestMethodInterface::METHOD_PATCH]
                )
            ) {
                if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
                    throw new UnsupportedMediaType();
                }
                $request = $request->withParsedBody($this->getBody());
            }
            $response = $handler->handle($request);
        } catch (BadRequest $exception) {
            $document = new Document($this->factory, $this->logger);
            $document->addError(Error::fromException($exception));
            $body = (new StreamFactory())->createStream(json_encode($document));
            $response = (new ResponseFactory())->createResponse($exception->getStatus())->withBody($body);
        }
        return $response->withHeader("Content-Type", Document::MEDIA_TYPE);
    }

    /**
     * @return mixed|null
     * @throws BadRequest
     */
    private function getBody()
    {
        if ($data = file_get_contents($this->streamPath)) {
            $body = json_decode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new BadRequest(json_last_error_msg());
            }
            // todo: Disabled cause weird behavior see
            //       https://discuss.jsonapi.org/t/json-schema-required-id-in-case-of-creating-resourceobject/1724
            //
            // $result = self::$validator->schemaValidation($body, self::$schema);
            // if (!$result->isValid()) {
            // $error = $result->getFirstError();
            // $msg = "Error: " . $error->keyword() . ' args: [' . print_r($error->keywordArgs(), true) . ']';
            //     throw new BadRequest($msg);
            // }
            return $body;
        }
        return null;
    }

    /**
     * @param string $path
     */
    public function setStream(string $path)
    {
        $this->streamPath = $path;
    }
}
