<?php

/**
 * Created by tomas
 * at 20.03.2021 15:32
 */

declare(strict_types=1);

namespace JSONAPI\Encoding;

use JSONAPI\Document\Id;
use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Document\Type;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Helper\DoctrineProxyTrait;
use JSONAPI\Metadata\MetadataRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Encoder
 *
 * @package JSONAPI\Encoding
 */
class Encoder
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
     * @var Processor[]
     */
    private array $processors;

    /**
     * Encoder constructor.
     *
     * @param MetadataRepository   $metadataRepository
     * @param LoggerInterface|null $logger
     * @param Processor[]          $processors
     */
    public function __construct(
        MetadataRepository $metadataRepository,
        LoggerInterface $logger = null,
        array $processors = [],
    ) {
        $this->repository = $metadataRepository;
        $this->logger     = $logger ?? new NullLogger();
        if (empty($processors)) {
            $this->processors = [
                new AttributesProcessor($metadataRepository, $logger),
                new RelationshipsProcessor($metadataRepository, $logger),
                new MetaProcessor($metadataRepository)
            ];
        } else {
            $this->processors = $processors;
        }
    }

    /**
     * @param object $object
     *
     * @return ResourceObject
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws MetadataNotFound
     * @throws JsonApiException
     */
    public function encode(object $object): ResourceObject
    {
        list($type, $id) = $this->getTypeAndId($object);
        $resource = new ResourceObject($type, $id);
        foreach ($this->processors as $processor) {
            $processor->process($resource, $object);
        }
        return $resource;
    }

    /**
     * @param object $object
     *
     * @return ResourceObjectIdentifier
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws MetadataNotFound
     */
    public function identify(object $object): ResourceObjectIdentifier
    {
        list($type, $id) = $this->getTypeAndId($object);
        $resource = new ResourceObjectIdentifier($type, $id);
        foreach ($this->processors as $processor) {
            $resource = $processor->process($resource, $object);
        }
        return $resource;
    }

    /**
     * @param object $object
     *
     * @return array{Type, Id}
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws MetadataNotFound
     */
    private function getTypeAndId(object $object): array
    {
        $className = Encoder::clearDoctrineProxyPrefix(get_class($object));
        $metadata  = $this->repository->getByClass($className);
        $type      = new Type($metadata->getType());
        if ($metadata->getId()->property != null) {
            $value = $object->{$metadata->getId()->property};
        } else {
            $value = (string)call_user_func([$object, $metadata->getId()->getter]);
        }
        $id = new Id($value);
        return [$type, $id];
    }
}
