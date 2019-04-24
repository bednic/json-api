<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:33
 */

namespace JSONAPI;

use Doctrine\Common\Util\ClassUtils;

use JSONAPI\Document;
use JSONAPI\Annotation;
use JSONAPI\Exception\DriverException;
use JSONAPI\Exception\EncoderException as EncoderExceptionAlias;
use JSONAPI\Exception\FactoryException;
use JSONAPI\Exception\UnsupportedMediaType;
use JSONAPI\Filter\Query;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;

/**
 * Class Encoder
 * @package JSONAPI
 */
class Encoder
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var LoggerInterface|NullLogger
     */
    private $logger;

    /**
     * @var object
     */
    private $object;

    /**
     * @var ReflectionClass
     */
    private $ref;

    /**
     * @var ClassMetadata
     */
    private $metadata = null;

    /**
     * Encoder constructor.
     * @param MetadataFactory $metadataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(MetadataFactory $metadataFactory, LoggerInterface $logger = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->logger = $logger ? $logger : new NullLogger();
    }

    /**
     * @param                     $object
     * @param EncoderOptions|null $options
     * @return Document\ResourceIdentifier
     * @throws DriverException
     * @throws EncoderExceptionAlias
     * @throws FactoryException
     */
    public function encode($object, EncoderOptions $options = null): Document\ResourceIdentifier
    {

        $options = $options ? $options : new EncoderOptions();
        $className = ClassUtils::getClass($object);
        $this->logger->debug("Init encoding of {$className}.");
        $encoder = clone $this;
        $encoder->object = $object;
        try {
            $encoder->ref = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new EncoderExceptionAlias("Class {$className} does not exist.");
        }
        $encoder->metadata = $this->metadataFactory->getMetadataByClass($className);
        $resource = new Document\ResourceIdentifier($encoder->getType(), $encoder->getId());
        if ($options->isFullLinkage()) {
            $resource = new Document\Resource($resource);
            $encoder->withFields($resource, $options->getQuery()->getFilter());

        }
        return $resource;

    }

    /**
     * @param RequestInterface $request
     * @return Document\Document
     * @throws EncoderExceptionAlias
     * @throws UnsupportedMediaType
     */
    public function decode(RequestInterface $request): Document\Document
    {
        $document = new Document\Document();
        $body = (string)$request->getBody();
        $meta = $request->getHeader('Content-Type');
        if (in_array(Document\Document::MEDIA_TYPE, $meta)) {
            $body = json_decode($body);
            if (is_array($body->data)) {
                $data = [];
                foreach ($body->data as $resourceDto) {
                    $resource = new Document\Resource(new Document\ResourceIdentifier($resourceDto->type, $resourceDto->id));
                    foreach ($resourceDto->attributes as $attribute => $value) {
                        $resource->addAttribute(new Document\Attribute($attribute, $value));
                    }

                    foreach ($resourceDto->relationships as $prop => $value) {
                        $value = $value->data;
                        $relationship = new Document\Relationship($prop, is_array($value));
                        if ($relationship->isCollection()) {
                            foreach ($value as $item) {
                                $relationship->addResource(new Document\ResourceIdentifier($item->type, $item->id));
                            }
                        } else {
                            $relationship->addResource(new Document\ResourceIdentifier($value->type, $value->id));
                        }
                        $resource->addRelationship($relationship);
                    }
                    $data[] = $resource;
                }
                $document->setData($data);
            } else {
                $resource = new Document\Resource(new Document\ResourceIdentifier($body->data->type, $body->data->id));
                foreach ($body->data->attributes as $attribute => $value) {
                    $resource->addAttribute(new Document\Attribute($attribute, $value));
                }
                foreach ($body->data->relationships as $prop => $value) {
                    $value = $value->data;
                    $relationship = new Document\Relationship($prop, is_array($value));
                    if ($relationship->isCollection()) {
                        foreach ($value as $item) {
                            $relationship->addResource(new Document\ResourceIdentifier($item->type, $item->id));
                        }
                    } else {
                        $relationship->addResource(new Document\ResourceIdentifier($value->type, $value->id));
                    }
                    $resource->addRelationship($relationship);
                }
                $document->setData($resource);
            }
        } else {
            throw new UnsupportedMediaType();
        }
        return $document;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->metadata->getResource()->type;
    }

    /**
     * @return string|int|null
     */
    public function getId()
    {
        try {
            if ($this->metadata->getId()->getter != null) {
                return call_user_func([$this->object, $this->metadata->getId()->getter]);
            } else {
                return $this->ref->getProperty($this->metadata->getId()->property)->getValue($this->object);
            }
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * @param Document\Resource $resource
     * @param array             $filter
     * @return Encoder
     * @throws DriverException
     * @throws EncoderExceptionAlias
     * @throws FactoryException
     */
    private function withFields(Document\Resource $resource, ?array $filter)
    {
        foreach (array_merge($this->metadata->getAttributes(), $this->metadata->getRelationships()) as $name => $field) {
            if (($filter && in_array($name, $filter)) || !$filter) {
                $value = null;
                if ($field->getter != null) {
                    $value = call_user_func([$this->object, $field->getter]);
                } else {
                    try {
                        $value = $this->ref->getProperty($field->property)->getValue($this->object);
                    } catch (ReflectionException $ignored) {
                        //NOSONAR
                    }
                }
                if ($field instanceof Annotation\Relationship) {
                    $relationship = new Document\Relationship($field->name, $field->isCollection);
                    if (is_iterable($value) && $field->isCollection) {
                        foreach ($value as $object) {
                            $relationship->addResource($this->encode($object));
                        }
                    } else {
                        $relationship->addResource($value ? $this->encode($value) : null);
                    }
                    $relationship->setLinks(
                        LinkProvider::createRelationshipsLinks(
                            new Document\ResourceIdentifier($this->getType(), $this->getId()),
                            $name
                        ));
                    $this->logger->debug("Adding relationship {$name}.");
                    $resource->addRelationship($relationship);
                } elseif ($field instanceof Annotation\Attribute) {
                    if ($value instanceof \DateTime) {
                        // ISO 8601
                        $value = $value->format(DATE_ATOM);
                    }
                    $this->logger->debug("Adding attribute {$name}.");
                    $resource->addAttribute(new Document\Attribute($name, $value));
                } else {
                    throw new EncoderExceptionAlias("Field {$name} is not Attribute nor Relationship");
                }
            }
        }
        return $this;
    }

}
