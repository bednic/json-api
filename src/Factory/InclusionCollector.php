<?php

declare(strict_types=1);

namespace JSONAPI\Factory;

use JSONAPI\Document\ResourceCollection;
use JSONAPI\Exception\Document\DocumentException;
use JSONAPI\Exception\Document\InclusionOverflow;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Exception\Metadata\RelationNotFound;
use JSONAPI\Helper\DoctrineProxyTrait;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Inclusion\Inclusion;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class InclusionCollector
 *
 * @package JSONAPI\Factory
 */
class InclusionCollector
{
    use DoctrineProxyTrait;

    /**
     * @var ResourceCollection
     */
    private ResourceCollection $included;
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;
    /**
     * @var Encoder
     */
    private Encoder $encoder;
    /**
     * @var int
     */
    private int $maxIncludedItems;

    /**
     * InclusionFetcher constructor.
     *
     * @param MetadataRepository   $metadataRepository
     * @param Encoder              $encoder
     * @param int                  $maxIncludedItems
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MetadataRepository $metadataRepository,
        Encoder $encoder,
        int $maxIncludedItems = 625,
        LoggerInterface $logger = null
    ) {
        $this->included           = new ResourceCollection();
        $this->metadataRepository = $metadataRepository;
        $this->encoder            = $encoder;
        $this->logger             = $logger ?? new NullLogger();
        $this->maxIncludedItems   = $maxIncludedItems;
    }

    /**
     * @param object      $object
     * @param Inclusion[] $inclusions
     *
     * @throws BadRequest
     * @throws DocumentException
     * @throws DriverException
     * @throws InclusionOverflow
     * @throws MetadataException
     */
    public function fetchInclusions(object $object, array $inclusions): void
    {
        $this->logger->debug('Fetching inclusions...');
        $classMetadata = $this->metadataRepository->getByClass(self::clearDoctrineProxyPrefix(get_class($object)));
        foreach ($inclusions as $sub) {
            try {
                $relationship = $classMetadata->getRelationship($sub->getRelationName());
                $data         = null;
                if ($relationship->property) {
                    $data = $object->{$relationship->property};
                } elseif ($relationship->getter) {
                    $data = call_user_func([$object, $relationship->getter]);
                }
                if (!empty($data)) {
                    if ($relationship->isCollection) {
                        foreach ($data as $item) {
                            $this->addInclusion($item);
                            if ($sub->hasInclusions()) {
                                $this->fetchInclusions($item, $sub->getInclusions());
                            }
                        }
                    } else {
                        $this->addInclusion($data);
                        if ($sub->hasInclusions()) {
                            $this->fetchInclusions($data, $sub->getInclusions());
                        }
                    }
                }
            } catch (RelationNotFound $relationNotFound) {
                throw new BadRequest("URL malformed around '{$sub->getRelationName()}'.");
            }
        }
    }

    /**
     * @param object $item
     *
     * @throws DocumentException
     * @throws MetadataException
     * @throws DriverException
     */
    private function addInclusion(object $item): void
    {
        if ($this->maxIncludedItems < 0 || $this->included->count() < $this->maxIncludedItems) {
            $this->included->add($this->encoder->getResource($item));
        } else {
            throw new InclusionOverflow($this->maxIncludedItems);
        }
    }

    /**
     * @return ResourceCollection
     */
    public function getIncluded(): ResourceCollection
    {
        return $this->included;
    }
}
