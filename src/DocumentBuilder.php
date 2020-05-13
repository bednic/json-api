<?php

declare(strict_types=1);

namespace JSONAPI;

use JSONAPI\Document\Document;
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
use JSONAPI\Uri\Inclusion\Inclusion;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\Pagination\UseTotalCount;
use JSONAPI\Uri\UriParser;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DocumentBuilder
{
    use DoctrineProxyTrait;

    private LoggerInterface $logger;
    private MetadataRepository $metadata;
    private Encoder $encoder;
    private Document $document;
    private UriParser $uri;

    private ResourceCollection $included;
    private int $maxIncludedItems;

    /**
     * DocumentBuilder constructor.
     *
     * @param MetadataRepository   $metadata
     * @param UriParser            $uriParser
     * @param LoggerInterface|null $logger
     * @param int                  $maxIncludedItems            Should by positive integer.
     *                                                          Disable limit by passing -1.
     * @param int                  $relationshipLimit           How many relationship object identifiers should be
     *                                                          included in relationship collection.
     */
    private function __construct(
        MetadataRepository $metadata,
        UriParser $uriParser,
        LoggerInterface $logger = null,
        int $maxIncludedItems = 625,
        int $relationshipLimit = 25
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->metadata = $metadata;
        $this->uri = $uriParser;
        $this->encoder = new Encoder($this->metadata, $this->uri->getFieldset(), $this->logger);
        $this->encoder->setRelationshipLimit($relationshipLimit);
        $this->maxIncludedItems = $maxIncludedItems;
        $this->document = new Document();
        $this->included = new ResourceCollection();
    }

    /**
     * @param MetadataRepository   $metadata
     * @param UriParser            $uriParser
     * @param LoggerInterface|null $logger
     * @param int                  $maxIncludedItems
     * @param int                  $relationshipLimit
     *
     * @return DocumentBuilder
     */
    public static function create(
        MetadataRepository $metadata,
        UriParser $uriParser,
        LoggerInterface $logger = null,
        int $maxIncludedItems = 625,
        int $relationshipLimit = 25
    ): self {
        return new self(
            $metadata,
            $uriParser,
            $logger,
            $maxIncludedItems,
            $relationshipLimit
        );
    }

    /**
     * @param iterable|object $data
     *
     * @return $this
     * @throws BadRequest
     * @throws DocumentException
     * @throws DriverException
     * @throws MetadataException
     */
    public function setData($data): self
    {
        $this->included->reset();
        if ($this->uri->getPath()->isCollection()) {
            $collection = new ResourceCollection();
            foreach ($data as $item) {
                if ($this->uri->getPath()->isRelationship()) {
                    $collection->add($this->encoder->getIdentifier($item));
                } else {
                    $collection->add($this->encoder->getResource($item));
                }
                if ($this->uri->getInclusion()->hasInclusions()) {
                    $this->fetchInclusions($item, $this->uri->getInclusion()->getInclusions());
                }
            }
            $this->document->setData($collection);
        } else {
            if ($this->uri->getPath()->isRelationship()) {
                $this->document->setData($this->encoder->getIdentifier($data));
            } else {
                $this->document->setData($this->encoder->getResource($data));
            }
            if ($this->uri->getInclusion()->hasInclusions()) {
                $this->fetchInclusions($data, $this->uri->getInclusion()->getInclusions());
            }
        }
        return $this;
    }

    /**
     * Use this method if you want auto adding of links like prev, next, last etc...
     * If you do not provide this information, builder will generate prev, next and first link without checking of
     * total threshold
     *
     * @param int $total
     *
     * @return $this
     */
    public function setTotalItems(int $total): self
    {
        if ($this->uri->getPagination() instanceof UseTotalCount) {
            $this->uri->getPagination()->setTotal($total);
        }
        return $this;
    }

    /**
     * @return Document
     * @throws BadRequest
     * @throws DocumentException
     */
    public function build(): Document
    {
        LinkFactory::setDocumentLinks($this->document, $this->uri);
        $this->document->setIncludes($this->included);
        return $this->document;
    }

    /**
     * @param object      $object
     * @param Inclusion[] $inclusions
     *
     * @throws BadRequest
     * @throws Exception\Document\ForbiddenCharacter
     * @throws Exception\Document\ForbiddenDataType
     * @throws Exception\Document\ReservedWord
     * @throws Exception\Document\ResourceTypeMismatch
     * @throws Exception\Driver\ClassNotExist
     * @throws Exception\Metadata\InvalidField
     * @throws Exception\Metadata\MetadataNotFound
     * @throws InclusionOverflow
     */
    private function fetchInclusions(object $object, array $inclusions): void
    {
        $classMetadata = $this->metadata->getByClass(self::clearDoctrineProxyPrefix(get_class($object)));
        foreach ($inclusions as $sub) {
            try {
                $relationship = $classMetadata->getRelationship($sub->getRelationName());
                $data = null;
                if ($relationship->property) {
                    $data = $object->{$relationship->property};
                } elseif ($relationship->getter) {
                    $data = call_user_func([$object, $relationship->getter]);
                }
                if (!empty($data)) {
                    if ($relationship->isCollection) {
                        foreach ($data as $item) {
                            if ($sub->hasInclusions()) {
                                $this->fetchInclusions($item, $sub->getInclusions());
                            } else {
                                $this->addInclusion($item);
                            }
                        }
                    } else {
                        if ($sub->hasInclusions()) {
                            $this->fetchInclusions($data, $sub->getInclusions());
                        } else {
                            $this->addInclusion($data);
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
     * @throws Exception\Document\ForbiddenCharacter
     * @throws Exception\Document\ForbiddenDataType
     * @throws Exception\Document\ReservedWord
     * @throws Exception\Driver\ClassNotExist
     * @throws Exception\Metadata\InvalidField
     * @throws Exception\Metadata\MetadataNotFound
     * @throws InclusionOverflow
     */
    private function addInclusion(object $item): void
    {
        if ($this->maxIncludedItems < 0 || $this->included->count() < $this->maxIncludedItems) {
            $this->included->add($this->encoder->getResource($item));
        } else {
            throw new InclusionOverflow($this->maxIncludedItems);
        }
    }
}
