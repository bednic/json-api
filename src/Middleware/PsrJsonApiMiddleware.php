<?php

declare(strict_types=1);

namespace JSONAPI\Middleware;

use Doctrine\Common\Collections\ArrayCollection;
use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Document\Attribute;
use JSONAPI\Document\Document;
use JSONAPI\Document\Error;
use JSONAPI\Document\Id;
use JSONAPI\Document\PrimaryData;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Document\Type;
use JSONAPI\Exception\Document\DocumentException;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Http\Conflict;
use JSONAPI\Exception\Http\UnsupportedMediaType;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Path\PathParser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Throwable;
use Tools\JSON\JsonDeserializable;

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
     * @var LoggerInterface|null
     */
    private LoggerInterface $logger;


    /**
     * PsrJsonApiMiddleware constructor.
     *
     * @param MetadataRepository       $repository
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface   $streamFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        MetadataRepository $repository,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        LoggerInterface $logger = null
    ) {
        $this->repository      = $repository;
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->logger          = $logger ?? new NullLogger();
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
                in_array($request->getMethod(), [
                RequestMethodInterface::METHOD_POST,
                RequestMethodInterface::METHOD_PATCH,
                RequestMethodInterface::METHOD_DELETE
                ])
            ) {
                if (!in_array(Document::MEDIA_TYPE, $request->getHeader("Content-Type"))) {
                    throw new UnsupportedMediaType();
                }
                $document = new Document();
                $request->getBody()->rewind();
                $data = $request->getBody()->getContents();
                if (strlen($data) > 0) {
                    $path = (new PathParser($this->repository, $request->getMethod()))
                        ->parse($request->getUri()->getPath());
                    $document->setData(
                        $this->loadRequestData(
                            json_decode($data, false, 512, JSON_THROW_ON_ERROR),
                            $path
                        )
                    );
                }

                $request = $request->withParsedBody($document);
            }
            $response = $handler->handle($request);
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception
            ]);
            $document = new Document();
            $error    = Error::fromException($exception);
            $document->addError($error);
            $response = $this->responseFactory
                ->createResponse($error->getStatus())
                ->withBody($this->streamFactory->createStream(json_encode($document)));
        }
        return $response->withHeader("Content-Type", Document::MEDIA_TYPE);
    }

    /**
     * @param stdClass|null $body
     * @param PathInterface $path
     *
     * @return PrimaryData|null
     * @throws BadRequest
     * @throws DocumentException
     * @throws MetadataException
     */
    private function loadRequestData(?stdClass $body, PathInterface $path): ?PrimaryData
    {
        if ($path->isCollection()) {
            $data = new ResourceCollection();
            if ($body) {
                $type     = $path->getPrimaryResourceType();
                $metadata = $this->repository->getByType($type);
                foreach ($body->data as $object) {
                    $resource = $this->jsonToResourceObject($object, $metadata, $path);
                    $data->add($resource);
                }
            }
        } else {
            $data = null;
            if ($body) {
                $type     = $path->getPrimaryResourceType();
                $metadata = $this->repository->getByType($type);
                $data     = $this->jsonToResourceObject($body->data, $metadata, $path);
            }
        }
        return $data;
    }

    /**
     * @param stdClass      $object
     * @param ClassMetadata $metadata
     * @param PathInterface $path
     *
     * @return ResourceObjectIdentifier|ResourceObject
     * @throws BadRequest
     * @throws DocumentException
     */
    private function jsonToResourceObject(
        stdClass $object,
        ClassMetadata $metadata,
        PathInterface $path
    ): ResourceObjectIdentifier {
        if ($object->type !== $metadata->getType()) {
            throw new Conflict();
        }
        $type     = new Type($object->type);
        $id       = new Id(@$object->id);
        $resource = new ResourceObjectIdentifier($type, $id);
        if (!$path->isRelationship()) {
            $resource = new ResourceObject($type, $id);
            foreach ($metadata->getAttributes() as $attribute) {
                if (isset($object->attributes->{$attribute->name})) {
                    $value = $object->attributes->{$attribute->name};
                    try {
                        $className = $attribute->type;
                        if ((new ReflectionClass($className))->implementsInterface(JsonDeserializable::class)) {
                            /** @var JsonDeserializable $className */
                            $value = $className::jsonDeserialize($value);
                        }
                    } catch (ReflectionException $ignored) {
                        //NOSONAR
                    }
                    $resource->addAttribute(new Attribute($attribute->name, $value));
                }
            }
            foreach ($metadata->getRelationships() as $relationship) {
                if (isset($object->relationships->{$relationship->name})) {
                    $value = $object->relationships->{$relationship->name}->data;
                    if ($relationship->isCollection) {
                        $data = new ArrayCollection();
                        foreach ($value as $item) {
                            $data->add(new ResourceObjectIdentifier(new Type($item->type), new Id($item->id)));
                        }
                    } else {
                        $data = new ResourceObjectIdentifier(new Type($value->type), new Id($value->id));
                    }
                    $resource->addRelationship(new Relationship($relationship->name, $data));
                }
            }
        }
        return $resource;
    }
}
