<?php

/**
 * Created by tomas
 * at 20.03.2021 21:18
 */

declare(strict_types=1);

namespace JSONAPI\Encoding;

use JSONAPI\Data\Collection;
use JSONAPI\Document\Relationship;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Exception\Document\AlreadyInUse;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Document\LinkComposer;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use JSONAPI\URI\Inclusion\InclusionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class RelationshipsProcessor
 *
 * @package JSONAPI\Encoding
 */
class RelationshipsProcessor extends FieldsProcessor implements Processor
{
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $repository;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var Encoder
     */
    private Encoder $encoder;
    /**
     * @var LinkComposer|null
     */
    private ?LinkComposer $linkFactory;
    /**
     * @var InclusionInterface|null
     */
    private ?InclusionInterface $inclusion;

    /**
     * @var bool
     */
    private bool $withData;
    /**
     * @var int
     */
    private int $limit;

    /**
     * RelationshipsProcessor constructor.
     *
     * @param MetadataRepository      $repository
     * @param LoggerInterface|null    $logger
     * @param LinkComposer|null       $linkFactory
     * @param InclusionInterface|null $inclusion
     * @param FieldsetInterface|null  $fieldset
     * @param bool                    $withData
     * @param int                     $dataLimit
     */
    public function __construct(
        MetadataRepository $repository,
        LoggerInterface $logger = null,
        LinkComposer $linkFactory = null,
        InclusionInterface $inclusion = null,
        FieldsetInterface $fieldset = null,
        bool $withData = true,
        int $dataLimit = PHP_INT_MAX
    ) {
        parent::__construct($fieldset);
        $this->repository  = $repository;
        $this->logger      = $logger ?? new NullLogger();
        $this->encoder     = new Encoder($repository, $logger, [new MetaProcessor($repository, $logger)]);
        $this->linkFactory = $linkFactory;
        $this->inclusion   = $inclusion;
        $this->withData    = $withData;
        $this->limit       = $dataLimit;
    }

    /**
     * @param ResourceObjectIdentifier|ResourceObject $resource
     * @param object                                  $object
     *
     * @return ResourceObjectIdentifier|ResourceObject
     * @throws ForbiddenDataType
     * @throws AlreadyInUse
     * @throws ForbiddenCharacter
     * @throws MetadataNotFound
     */
    public function process(
        ResourceObjectIdentifier | ResourceObject $resource,
        object $object
    ): ResourceObjectIdentifier | ResourceObject {
        if ($resource instanceof ResourceObject) {
            $metadata = $this->repository->getByType($resource->getType());
            foreach ($metadata->getRelationships() as $field) {
                if ($this->showField($field, $resource)) {
                    $relationship = new Relationship($field->name);
                    if ($this->withData()) {
                        $value = $this->getValue($field, $object);
                        $this->setRelationshipData($field, $relationship, $value);
                    }
                    $this->linkFactory?->setRelationshipLinks($relationship, $resource);
                    if ($field->meta) {
                        $this->logger->debug("Adding meta to relationship {$field->name}");
                        $relationship->setMeta(call_user_func([$object, $field->meta->getter]));
                    }
                    $resource->addRelationship($relationship);
                    $this->logger->debug("Adding relationship {$field->name}.");
                }
            }
        }
        return $resource;
    }


    /**
     * @param \JSONAPI\Metadata\Relationship $field
     * @param Relationship                   $relationship
     * @param mixed                          $value
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws MetadataNotFound
     */
    private function setRelationshipData(
        \JSONAPI\Metadata\Relationship $field,
        Relationship $relationship,
        mixed $value
    ): void {
        if ($field->isCollection) {
            if (!($value instanceof Collection)) {
                $value = new Collection($value);
            }
            $data = new ResourceCollection();
            foreach ($value->slice(0, min($this->limit, $value->count())) as $object) {
                $data->add($this->encoder->identify($object));
            }
        } elseif ($value) {
            $data = $this->encoder->identify($value);
        } else {
            $data = null;
        }
        $relationship->setData($data);
    }

    /**
     * @return bool
     */
    private function withData(): bool
    {
        return $this->withData || $this->inclusion?->hasInclusions();
    }
}
