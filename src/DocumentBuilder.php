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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Psr7\Factory\ServerRequestFactory;

class DocumentBuilder
{
    use DoctrineProxyTrait;

    private LoggerInterface $logger;
    private MetadataRepository $metadata;
    private Encoder $encoder;
    private Document $document;
    private UriParser $uri;

    private ResourceCollection $included;

    /**
     * DocumentBuilder constructor.
     *
     * @param MetadataRepository   $metadata
     * @param UriParser            $uriParser
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MetadataRepository $metadata,
        UriParser $uriParser,
        LoggerInterface $logger = null
    ) {
        $this->metadata = $metadata;
        $this->uri      = $uriParser;
        $this->logger   = $logger ?? new NullLogger();
        $this->encoder  = new Encoder($this->metadata, $this->uri->getFieldset(), $this->logger);
        $this->document = new Document();
        $this->included = new ResourceCollection();
    }

    /**
     * Come handy in simple use-cases
     *
     * @param MetadataRepository     $metadataRepository
     * @param ServerRequestInterface $request
     * @param LoggerInterface|null   $logger
     *
     * @return DocumentBuilder
     * @throws BadRequest
     */
    public function create(
        MetadataRepository $metadataRepository,
        ServerRequestInterface $request,
        LoggerInterface $logger = null
    ) {
        return new DocumentBuilder($metadataRepository, new UriParser($request, $metadataRepository), $logger);
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
        $this->logger->debug('Setting data.');
        $this->included->reset();
        $origin = Config::$RELATIONSHIP_DATA; // backup
        if ($this->uri->getInclusion()->hasInclusions()) {
            Config::$RELATIONSHIP_DATA = true; // overload
        }
        if ($this->uri->getPath()->isCollection()) {
            $this->logger->debug('It is resource collection.');
            $collection = new ResourceCollection();
            foreach ($data as $item) {
                if ($this->uri->getPath()->isRelationship()) {
                    $collection->add($this->encoder->getIdentifier($item));
                } else {
                    $collection->add($this->encoder->getResource($item));
                }
            }
            $this->document->setData($collection);
        } else {
            $this->logger->debug('It is single resource');
            if ($this->uri->getPath()->isRelationship()) {
                $this->document->setData($this->encoder->getIdentifier($data));
            } else {
                $this->document->setData($this->encoder->getResource($data));
            }
            if ($this->uri->getInclusion()->hasInclusions()) {
                $this->fetchInclusions($data, $this->uri->getInclusion()->getInclusions());
            }
        }
        Config::$RELATIONSHIP_DATA = $origin; // reset
        $this->logger->debug('Data set.');
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
        $this->logger->debug('Setting total.');
        if ($this->uri->getPagination() instanceof UseTotalCount) {
            $this->uri->getPagination()->setTotal($total);
            $this->logger->debug('Total set.');
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
        $this->logger->debug('Building doc.');
        LinkFactory::setDocumentLinks($this->document, $this->uri);
        $this->logger->debug('Links set.');
        $this->document->setIncludes($this->included);
        $this->logger->debug('Includes set.');
        $this->logger->debug('Built.');
        return $this->document;
    }

    /**
     * @param object      $object
     * @param Inclusion[] $inclusions
     *
     * @throws BadRequest
     * @throws Exception\Document\ForbiddenCharacter
     * @throws Exception\Document\ForbiddenDataType
     * @throws Exception\Document\AlreadyInUse
     * @throws Exception\Document\ResourceTypeMismatch
     * @throws Exception\Driver\ClassNotExist
     * @throws Exception\Metadata\InvalidField
     * @throws Exception\Metadata\MetadataNotFound
     * @throws InclusionOverflow
     */
    private function fetchInclusions(object $object, array $inclusions): void
    {
        $this->logger->debug('Fetching inclusions...');
        $classMetadata = $this->metadata->getByClass(self::clearDoctrineProxyPrefix(get_class($object)));
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
     * @throws Exception\Document\ForbiddenCharacter
     * @throws Exception\Document\ForbiddenDataType
     * @throws Exception\Document\AlreadyInUse
     * @throws Exception\Driver\ClassNotExist
     * @throws Exception\Metadata\InvalidField
     * @throws Exception\Metadata\MetadataNotFound
     * @throws InclusionOverflow
     */
    private function addInclusion(object $item): void
    {
        if (Config::$MAX_INCLUDED_ITEMS < 0 || $this->included->count() < Config::$MAX_INCLUDED_ITEMS) {
            $this->included->add($this->encoder->getResource($item));
        } else {
            throw new InclusionOverflow(Config::$MAX_INCLUDED_ITEMS);
        }
    }
}
