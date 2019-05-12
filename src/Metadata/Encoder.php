<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:33
 */

namespace JSONAPI\Metadata;

use DateTime;
use Doctrine\Common\Util\ClassUtils;

use JSONAPI\Document;
use JSONAPI\Annotation;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Exception\DocumentException;
use JSONAPI\Exception\DriverException;
use JSONAPI\Exception\EncoderException;
use JSONAPI\Exception\FactoryException;
use JSONAPI\Query\LinkProvider;
use JSONAPI\Query\Query;
use JSONAPI\Query\QueryFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;

/**
 * Class Encoder
 *
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
     * @var Query
     */
    private $query;

    /**
     * Encoder constructor.
     *
     * @param MetadataFactory $metadataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(MetadataFactory $metadataFactory, LoggerInterface $logger = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->logger = $logger ?? new NullLogger();
        $this->query = QueryFactory::create();
    }

    /**
     * @param $object
     * @return ResourceObject
     * @throws DocumentException
     * @throws DriverException
     * @throws EncoderException
     * @throws FactoryException
     */
    public function encode($object): ResourceObject
    {
        $encoder = $this->for($object);
        $resource = new ResourceObject($encoder->getIdentifier());
        $encoder->setFields($resource);
        $resource->addLink(LinkProvider::createSelfLink($resource));
        return $resource;
    }

    /**
     * @return string
     */
    private function getType(): string
    {
        return $this->metadata->getResource()->type;
    }

    /**
     * @return string|int|null
     */
    private function getId()
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
     * @param Document\ResourceObject $resourceObject
     * @throws DocumentException
     * @throws DriverException
     * @throws EncoderException
     * @throws FactoryException
     */
    private function setFields(Document\ResourceObject $resourceObject): void
    {
        $fields = $this->query->getFieldsFor($resourceObject->getType());
        foreach (array_merge($this->metadata->getAttributes()->toArray(), $this->metadata->getRelationships()->toArray()) as $name => $field) {
            if (($fields && in_array($name, $fields)) || !$fields) {
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
                    $data = null;
                    if ($field->isCollection) {
                        $data = [];
                        foreach ($value as $object) {
                            $data[] = $this->for($object)->getIdentifier();
                        }
                    } elseif ($value) {
                        $data = $this->for($value)->getIdentifier();
                    }

                    $relationship = new Document\Relationship($field->name, $data);
                    $relationship->addLink(LinkProvider::createSelfLink($resourceObject, $relationship));
                    $relationship->addLink(LinkProvider::createRelatedLink($resourceObject, $relationship));
                    $resourceObject->addRelationship($relationship);
                    $this->logger->debug("Adding relationship {$name}.");
                } elseif ($field instanceof Annotation\Attribute) {
                    if ($value instanceof DateTime) {
                        // ISO 8601
                        $value = $value->format(DATE_ATOM);
                    }
                    $this->logger->debug("Adding attribute {$name}.");
                    $resourceObject->addAttribute(new Document\Attribute($name, $value));
                } else {
                    throw new EncoderException(
                        "Field {$name} is not Attribute nor Relationship",
                        EncoderException::INVALID_FIELD
                    );
                }
            }
        }
    }

    /**
     * @return ResourceObjectIdentifier
     */
    private function getIdentifier(): ResourceObjectIdentifier
    {
        return new ResourceObjectIdentifier($this->getType(), $this->getId());
    }

    /**
     * @param $object
     * @return Encoder
     * @throws DriverException
     * @throws EncoderException
     * @throws FactoryException
     */
    private function for($object): Encoder
    {
        $className = ClassUtils::getClass($object);
        $this->logger->debug("Init encoding of {$className}.");
        $encoder = clone $this;
        $encoder->object = $object;

        try {
            $encoder->ref = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new EncoderException(
                "Class {$className} does not exist.",
                EncoderException::CLASS_NOT_EXIST
            );
        }
        $encoder->metadata = $this->metadataFactory->getMetadataByClass($className);
        return $encoder;
    }
}
