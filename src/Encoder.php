<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:33
 */

namespace JSONAPI;

use Doctrine\Common\Util\ClassUtils;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Document\Fields;
use JSONAPI\Document\Link;
use JSONAPI\Document\Resource;
use JSONAPI\Document\ResourceIdentifier;
use JSONAPI\Exception\ClassMetadataException;
use JSONAPI\Exception\NullException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
     * @var \ReflectionClass
     */
    private $ref;

    /**
     * @var ClassMetadata
     */
    private $metadata = null;

    /**
     * @var Fields
     */
    private $fields = null;

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
     * @param $object
     * @return Encoder
     * @throws ClassMetadataException
     * @throws NullException
     */
    public function create($object): Encoder
    {
        return $this->__invoke($object);
    }

    /**
     * @param $object
     * @return Encoder
     * @throws ClassMetadataException
     * @throws NullException
     */
    public function __invoke($object): Encoder
    {
        $that = new self($this->metadataFactory, $this->logger);
        // todo: this is only because using doctrine proxy
        $className = ClassUtils::getClass($object);
        $that->object = $object;
        $this->logger->debug("Init encoding of {$className}.");
        try {
            $that->ref = new \ReflectionClass($className);
            $that->metadata = $this->metadataFactory->getMetadataByClass($className);
        } catch (\ReflectionException $e) {
            throw new NullException("Class {$className} doesn't exists");
        }
        return $that;
    }

    /**
     * @param array $filter
     * @return Encoder
     * @throws ClassMetadataException
     * @throws NullException
     */
    public function withFields(array $filter = null): Encoder
    {
        try {
            $this->fields = new Fields();
            foreach (array_merge($this->metadata->getAttributes(), $this->metadata->getRelationships()) as $name => $field) {
                if ($filter && in_array($name, $filter) || !$filter) {
                    if ($field->getter != null) {
                        $value = call_user_func([$this->object, $field->getter]);
                    } else {
                        $value = $this->ref->getProperty($field->property)->getValue($this->object);
                    }
                    if ($field instanceof Relationship) {
                        if (is_iterable($value)) {
                            $relationships = new Document\Relationship($field->isCollection);
                            foreach ($value as $object) {
                                $relationships->addResource($this($object)->encode());
                            }
                        } else {
                            $relationships = new Document\Relationship($field->isCollection, $value ? $this($value)->encode() : null);

                        }
                        $relationships->setLinks(Link::createRelationshipsLinks(new ResourceIdentifier($this->getType(), $this->getId()), $name));
                        $value = $relationships;
                        $this->logger->debug("Field {$name} is relationship");
                    }
                    if ($value instanceof \DateTime) {
                        // ISO 8601
                        $value = $value->format(DATE_ATOM);
                    }
                    $this->logger->debug("Adding field {$name}");
                    $this->fields->addField($name, $value);
                }
            }
        } catch (\ReflectionException $e) {
            throw new NullException($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }

    /**
     * @return ResourceIdentifier | Resource
     */
    public function encode()
    {
        $type = $this->getType();
        $id = $this->getId();
        $identifier = new ResourceIdentifier($type, $id);
        $this->logger->debug("Encoding resource <{$type}>[{$id}]");
        if ($this->fields) {
            return new Resource($identifier, $this->fields);
        } else {
            return $identifier;
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->metadata->getResource()->type;
    }

    /**
     * @return int|string|null
     */
    public function getId()
    {
        try {
            if ($this->metadata->getId()->getter != null) {
                return call_user_func([$this->object, $this->metadata->getId()->getter]);
            } else {
                return $this->ref->getProperty($this->metadata->getId()->property)->getValue($this->object);
            }
        } catch (\ReflectionException $e) {
            return null;
        }
    }

}
