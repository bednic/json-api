<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Helper\DoctrineProxyTrait;
use JSONAPI\Document;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Document\AlreadyInUse;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Metadata\InvalidField;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Inclusion\InclusionInterface;
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
     * @var FieldsetInterface
     */
    private FieldsetInterface $fieldset;

    /**
     * @var InclusionInterface
     */
    private InclusionInterface $inclusion;

    /**
     * @var LinkFactory
     */
    private LinkFactory $linkFactory;

    /**
     * @var bool
     */
    private bool $withData;

    /**
     * @var int
     */
    private int $relationshipLimit;

    /**
     * Encoder constructor.
     *
     * @param MetadataRepository   $metadataRepository
     * @param FieldsetInterface    $fieldset
     * @param InclusionInterface   $inclusion
     * @param LinkFactory          $linkFactory
     * @param bool                 $relationshipData
     * @param int                  $relationshipLimit
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MetadataRepository $metadataRepository,
        FieldsetInterface $fieldset,
        InclusionInterface $inclusion,
        LinkFactory $linkFactory,
        bool $relationshipData = true,
        int $relationshipLimit = 25,
        LoggerInterface $logger = null
    ) {
        $this->repository        = $metadataRepository;
        $this->fieldset          = $fieldset;
        $this->inclusion         = $inclusion;
        $this->logger            = $logger ?? new NullLogger();
        $this->linkFactory       = $linkFactory;
        $this->withData          = $relationshipData;
        $this->relationshipLimit = $relationshipLimit;
    }


    /**
     * @param object $object
     *
     * @return ResourceObjectIdentifier
     * @throws ClassNotExist
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws MetadataNotFound
     */
    public function getIdentifier(object $object): ResourceObjectIdentifier
    {
        return $this->for($object)->createIdentifier()->setMeta()->resource;
    }

    /**
     * @param object $object
     *
     * @return ResourceObject
     * @throws ClassNotExist
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidField
     * @throws MetadataNotFound
     * @throws AlreadyInUse
     */
    public function getResource(object $object): ResourceObject
    {
        return $this->for($object)->createResource()->setMeta()->setFields()->setLinks()->resource;
    }

    /**
     * @return $this
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function createIdentifier(): Encoder
    {
        $this->resource = new ResourceObjectIdentifier($this->getType(), $this->getId());
        return $this;
    }

    /**
     * @return self
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function createResource(): Encoder
    {
        $this->resource = new ResourceObject($this->getType(), $this->getId());
        return $this;
    }

    /**
     * @return $this
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    private function setLinks(): Encoder
    {
        $this->linkFactory->setResourceLink($this->resource);
        return $this;
    }

    /**
     * @param object $object
     *
     * @return Encoder
     * @throws ClassNotExist
     * @throws MetadataNotFound
     */
    private function for(object $object): Encoder
    {
        $encoder   = clone $this;
        $className = Encoder::clearDoctrineProxyPrefix(get_class($object));
        try {
            $this->logger->debug("Init encoding of {$className}.");
            $encoder->object   = $object;
            $encoder->metadata = $this->repository->getByClass($className);
            $encoder->ref      = new ReflectionClass($className);
        } catch (ReflectionException $exception) {
            throw new ClassNotExist($className);
        }
        return $encoder;
    }

    /**
     * @return Document\Type
     * @throws ForbiddenCharacter
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
    private function setMeta(): Encoder
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
     * @throws AlreadyInUse
     */
    private function setFields(): Encoder
    {
        if ($this->resource instanceof ResourceObject) {
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
                        $relationship = new Document\Relationship($field->name);
                        if ($this->withData || $this->inclusion->hasInclusions()) {
                            if ($field->isCollection) {
                                if (!($value instanceof Collection)) {
                                    $value = new ArrayCollection($value);
                                }
                                /** @var Collection $value */
                                $data  = new ArrayCollection();
                                $total = $value->count();
                                $limit = min($this->relationshipLimit, $total);
                                foreach ($value->slice(0, $limit) as $object) {
                                    $data->add($this->getIdentifier($object));
                                }
                            } elseif ($value) {
                                $data = $this->getIdentifier($value);
                            } else {
                                $data = null;
                            }
                            $relationship->setData($data);
                        }
                        $this->linkFactory->setRelationshipLinks($relationship, $this->resource);
                        if ($field->meta) {
                            $this->logger->debug("Adding meta to relationship {$name}");
                            $relationship->setMeta(call_user_func([$this->object, $field->meta->getter]));
                        }
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
        }
        return $this;
    }
}
