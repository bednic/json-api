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
use JSONAPI\Annotation;
use JSONAPI\Document;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Encoder\InvalidField;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Query\LinkProvider;
use JSONAPI\Query\Query;
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
     * @var int
     */
    private $relationshipLimit;

    /**
     * Encoder constructor.
     *
     * @param MetadataFactory $metadataFactory
     * @param Query           $query
     * @param LoggerInterface $logger
     * @param int             $relationshipLimit
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        Query $query = null,
        LoggerInterface $logger = null,
        int $relationshipLimit = 25
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->logger = $logger ?? new NullLogger();
        $this->query = $query ?? new Query();
        $this->relationshipLimit = $relationshipLimit;
    }

    /**
     * @param $object
     *
     * @return ResourceObjectIdentifier
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws ForbiddenDataType
     */
    public function identify($object): ResourceObjectIdentifier
    {
        $encoder = $this->for($object);
        return $encoder->getIdentifier();
    }

    /**
     * @param $object
     *
     * @return ResourceObject
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     * @throws InvalidField
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
     * @return string|null
     */
    private function getId(): ?string
    {
        try {
            if ($this->metadata->getId()->getter != null) {
                return (string)call_user_func([$this->object, $this->metadata->getId()->getter]);
            } else {
                return (string)$this->ref->getProperty($this->metadata->getId()->property)->getValue($this->object);
            }
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * @param ResourceObject $resourceObject
     *
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     * @throws InvalidField
     * @throws ForbiddenCharacter
     */
    private function setFields(Document\ResourceObject $resourceObject): void
    {
        $fields = $this->query->getFieldsFor($resourceObject->getType());
        foreach (array_merge(
            $this->metadata->getAttributes()->toArray(),
            $this->metadata->getRelationships()->toArray()
        ) as $name => $field) {
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
                    $meta = null;
                    if ($field->isCollection) {
                        $data = [];
                        $total = count($value);
                        $limit = min($this->relationshipLimit, $total);
                        $counter = $limit;
                        foreach ($value as $object) {
                            $data[] = $this->for($object)->getIdentifier();
                            $counter--;
                            if ($counter === 0) {
                                break;
                            }
                        }
                        if ($total > $limit) {
                            $meta = new Document\Meta();
                            $meta->addField('total', $total);
                            $meta->addField('limit', $limit);
                            $meta->addField('offset', 0);
                        }
                    } elseif ($value) {
                        $data = $this->for($value)->getIdentifier();
                    }

                    $relationship = new Document\Relationship($field->name, $data);
                    $relationship->setLinks([
                        LinkProvider::createRelationshipLink($resourceObject, $relationship, $meta),
                        LinkProvider::createRelatedLink($resourceObject, $relationship)
                    ]);
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
                    throw new InvalidField($name);
                }
            }
        }
    }

    /**
     * @return ResourceObjectIdentifier
     * @throws ForbiddenDataType
     */
    private function getIdentifier(): ResourceObjectIdentifier
    {
        return new ResourceObjectIdentifier($this->getType(), $this->getId());
    }

    /**
     * @param $object
     *
     * @return Encoder
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     */
    private function for($object): Encoder
    {
        $encoder = clone $this;
        try {
            $className = ClassUtils::getClass($object);
            $this->logger->debug("Init encoding of {$className}.");
            $encoder->object = $object;
            $encoder->metadata = $this->metadataFactory->getMetadataByClass($className);
            $encoder->ref = new ReflectionClass($className);
        } catch (ReflectionException $exception) { //NOSONAR
        }
        return $encoder;
    }
}
