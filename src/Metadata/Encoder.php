<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\DoctrineProxyTrait;
use JSONAPI\Document;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Document\ReservedWord;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\Metadata\InvalidField;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\LinkFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;

/**
 * Class Encoder
 *
 * @package JSONAPI
 */
final class Encoder
{
    use DoctrineProxyTrait;

    /**
     * @var MetadataRepository
     */
    private MetadataRepository $repository;


    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ResourceObjectIdentifier|ResourceObject
     */
    private ResourceObjectIdentifier $resource;

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
     * Encoder constructor.
     *
     * @param MetadataRepository $metadataRepository
     * @param FieldsetInterface  $fieldset
     * @param LoggerInterface    $logger
     */
    public function __construct(
        MetadataRepository $metadataRepository,
        FieldsetInterface $fieldset,
        LoggerInterface $logger = null
    ) {
        $this->repository = $metadataRepository;
        $this->fieldset = $fieldset;
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
     * @throws ClassNotExist
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws MetadataNotFound
     */
    public function getIdentifier($object): ResourceObjectIdentifier
    {
        return $this->for($object)->createIdentifier()->setMeta()->resource;
    }

    /**
     * @param $object
     *
     * @return ResourceObject
     * @throws ClassNotExist
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidField
     * @throws MetadataNotFound
     * @throws ReservedWord
     */
    public function getResource($object): ResourceObject
    {
        return $this->for($object)->createResource()->setMeta()->setFields()->setLinks()->resource;
    }

    /**
     * @return $this
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function createIdentifier(): self
    {
        $this->resource = new ResourceObjectIdentifier($this->getType(), $this->getId());
        return $this;
    }

    /**
     * @return $this
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function createResource(): self
    {
        $this->resource = new ResourceObject($this->getType(), $this->getId());
        return $this;
    }

    /**
     * @return $this
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function setLinks(): self
    {
        LinkFactory::setResourceLink($this->resource);
        return $this;
    }

    /**
     * @param $object
     *
     * @return Encoder
     * @throws ClassNotExist
     * @throws MetadataNotFound
     */
    private function for($object): Encoder
    {
        $encoder = clone $this;
        $className = self::clearDoctrineProxyPrefix(get_class($object));
        try {
            $this->logger->debug("Init encoding of {$className}.");
            $encoder->object = $object;
            $encoder->metadata = $this->repository->getByClass($className);
            $encoder->ref = new ReflectionClass($className);
        } catch (ReflectionException $exception) {
            throw new ClassNotExist($className);
        }
        return $encoder;
    }

    /**
     * @return Document\Type
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function getType(): Document\Type
    {
        return new Document\Type($this->metadata->getType());
    }

    /**
     * @return Document\Id
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function getId(): Document\Id
    {
        $value = null;
        if ($this->metadata->getId()->property != null) {
            try {
                $value = (string)$this->ref->getProperty($this->metadata->getId()->property)->getValue($this->object);
            } catch (ReflectionException $ignored) {
                // NO SONAR
            }
        } else {
            $value = (string)call_user_func([$this->object, $this->metadata->getId()->getter]);
        }
        return new Document\Id($value);
    }

    /**
     * @return $this
     */
    private function setMeta(): self
    {
        if ($meta = $this->metadata->getMeta()) {
            $meta = call_user_func([$this->object, $meta->getter]);
            $this->resource->setMeta($meta);
        }
        return $this;
    }

    /**
     * @return $this
     * @throws ClassNotExist
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidField
     * @throws MetadataNotFound
     * @throws ReservedWord
     */
    private function setFields(): self
    {
        foreach (
            array_merge(
                $this->metadata->getAttributes(),
                $this->metadata->getRelationships()
            ) as $name => $field
        ) {
            if ($this->fieldset->showField($this->resource->getType(), $name)) {
                $value = null;
                if ($field->getter != null) {
                    $value = call_user_func([$this->object, $field->getter]);
                } else {
                    try {
                        $value = $this->ref->getProperty($field->property)->getValue($this->object);
                    } catch (ReflectionException $ignored) {
                        // NO SONAR Can't happen
                    }
                }
                if ($field instanceof Relationship) {
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
                            $data->add($this->getIdentifier($object));
                        }
                    } elseif ($value) {
                        $data = $this->getIdentifier($value);
                    }
                    $relationship = new Document\Relationship($field->name, $data);
                    LinkFactory::setRelationshipLinks($relationship, $this->resource);
                    $relationship->setMeta($meta);
                    $this->resource->addRelationship($relationship);
                    $this->logger->debug("Adding relationship {$name}.");
                } elseif ($field instanceof Attribute) {
                    if ($value instanceof DateTimeInterface) {
                        // ISO 8601
                        $value = $value->format(DATE_ATOM);
                    }
                    $this->logger->debug("Adding attribute {$name}.");
                    $this->resource->addAttribute(new Document\Attribute($name, $value));
                } else {
                    throw new InvalidField($name);
                }
            }
        }
        return $this;
    }
}
