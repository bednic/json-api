<?php

/**
 * Created by tomas
 * at 20.03.2021 20:34
 */

declare(strict_types=1);

namespace JSONAPI\Encoding;

use JSONAPI\Document\ResourceObject;
use JSONAPI\Document\ResourceObjectIdentifier;
use JSONAPI\Metadata\MetadataRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class MetaProcessor
 *
 * @package JSONAPI\Encoding
 */
class MetaProcessor implements Processor
{
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $repository;
    /**
     * @var LoggerInterface
     */
    private readonly LoggerInterface $logger;

    /**
     * MetaProcessor constructor.
     *
     * @param MetadataRepository   $repository
     * @param LoggerInterface|null $logger
     */
    public function __construct(MetadataRepository $repository, LoggerInterface $logger = null)
    {
        $this->repository = $repository;
        $this->logger = $logger ?? new NullLogger();
    }

    public function process(
        ResourceObjectIdentifier | ResourceObject $resource,
        object $object
    ): ResourceObjectIdentifier | ResourceObject {
        $metadata = $this->repository->getByType($resource->getType());
        if ($meta = $metadata->getMeta()) {
            $this->logger->debug("Found meta for {$resource->getType()}");
            $meta = call_user_func([$object, $meta->getter]);
            $resource->setMeta($meta);
        }
        return $resource;
    }
}
