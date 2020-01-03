<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:33
 */

namespace JSONAPI\Metadata;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Annotation;
use JSONAPI\DoctrineProxyTrait;
use JSONAPI\Document;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\Metadata\InvalidField;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Path\PathParser;
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
    use DoctrineProxyTrait;

    /**
     * @var MetadataFactory
     */
    private MetadataFactory $metadataFactory;


    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var object
     */
    private object $object;

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $ref;

    /**
     * @var ClassMetadata
     */
    private ClassMetadata $metadata;

    /**
     * @var int
     */
    private int $relationshipLimit = 25;

    /**
     * @var FieldsetInterface
     */
    private FieldsetInterface $fieldset;

    /**
     * @var LinkFactory
     */
    private LinkFactory $linkFactory;

    /**
     * Encoder constructor.
     *
     * @param MetadataFactory   $metadataFactory
     * @param FieldsetInterface $fieldset
     * @param LinkFactory       $linkFactory
     * @param LoggerInterface   $logger
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        FieldsetInterface $fieldset,
        LinkFactory $linkFactory,
        LoggerInterface $logger = null
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->fieldset = $fieldset;
        $this->linkFactory = $linkFactory;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return int
     */
    public function getRelationshipLimit(): int
    {
        return $this->relationshipLimit;
    }

    /**
     * @param int $relationshipLimit
     */
    public function setRelationshipLimit(int $relationshipLimit): void
    {
        $this->relationshipLimit = $relationshipLimit;
    }

    /**
     * @param $object
     *
     * @return ResourceObjectIdentifier
     * @throws DriverException
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     */
    public function identify($object): ResourceObjectIdentifier
    {
        $encoder = $this->for($object);
        return $encoder->getIdentifier();
    }

    /**
     * @param $object
     *
     * @return Encoder
     * @throws DriverException
     * @throws InvalidArgumentException
     */
    private function for($object): Encoder
    {
        $encoder = clone $this;
        try {
            $className = self::clearDoctrineProxyPrefix(get_class($object));
            $this->logger->debug("Init encoding of {$className}.");
            $encoder->object = $object;
            $encoder->metadata = $this->metadataFactory->getMetadataByClass($className);
            $encoder->ref = new ReflectionClass($className);
        } catch (ReflectionException $exception) { //NOSONAR
        }
        return $encoder;
    }

    /**
     * @return ResourceObjectIdentifier
     * @throws ForbiddenDataType
     */
    private function getIdentifier(): ResourceObjectIdentifier
    {
        $identifier = new ResourceObjectIdentifier($this->getType(), $this->getId());
        $this->setMeta($identifier);
        return $identifier;
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
     * @param ResourceObjectIdentifier $identifier
     */
    private function setMeta(ResourceObjectIdentifier $identifier)
    {
        if ($meta = $this->metadata->getResource()->meta) {
            $meta = call_user_func([$this->object, $meta->getter]);
            $identifier->setMeta($meta);
        }
    }

    /**
     * @param $object
     *
     * @return ResourceObject
     * @throws DriverException
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    public function encode($object): ResourceObject
    {
        $encoder = $this->for($object);
        $resource = new ResourceObject($encoder->getIdentifier());
        $encoder->setFields($resource);
        $resource->addLink($this->linkFactory->getResourceLink($resource));
        return $resource;
    }

    /**
     * @param ResourceObject $resourceObject
     *
     * @throws DriverException
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     * @throws InvalidField
     * @throws MetadataException
     */
    private function setFields(Document\ResourceObject $resourceObject): void
    {
        foreach (
            array_merge(
                $this->metadata->getAttributes()->toArray(),
                $this->metadata->getRelationships()->toArray()
            ) as $name => $field
        ) {
            if ($this->fieldset->showField($resourceObject->getType(), $name)) {
                $value = null;
                if ($field->getter != null) {
                    $value = call_user_func([$this->object, $field->getter]);
                } else {
                    try {
                        $value = $this->ref->getProperty($field->property)->getValue($this->object);
                    } catch (ReflectionException $exception) {
                        throw new MetadataException("Unknown Metadata Exception",540, $exception);
                    }
                }
                if ($field instanceof Annotation\Relationship) {
                    $data = null;
                    $meta = new Document\Meta();
                    if ($field->meta) {
                        $meta = call_user_func([$this->object, $field->meta->getter]);
                    }
                    if ($field->isCollection) {
                        if (!($value instanceof Collection)) {
                            $value = new ArrayCollection($value);
                        }
                        /** @var Collection $value */
                        $data = new ArrayCollection();
                        $total = $value->count();
                        $meta->setProperty('total', $total);
                        $limit = min($this->relationshipLimit, $total);
                        foreach ($value->slice(0, $limit) as $object) {
                            $data->add($this->for($object)->getIdentifier());
                        }

                    } elseif ($value) {
                        $data = $this->for($value)->getIdentifier();
                    }
                    $relationship = new Document\Relationship($field->name, $data);
                    $relationship->addLink($this->linkFactory->getRelationshipLink($relationship, $resourceObject));
                    $relationship->addLink($this->linkFactory->getRelationLink($relationship, $resourceObject));
                    $relationship->setMeta($meta);
                    $resourceObject->addRelationship($relationship);
                    $this->logger->debug("Adding relationship {$name}.");
                } elseif ($field instanceof Annotation\Attribute) {
                    if ($value instanceof DateTimeInterface) {
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
}
