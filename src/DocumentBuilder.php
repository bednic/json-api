<?php

declare(strict_types=1);

namespace JSONAPI;

use JSONAPI\Document\Document;
use JSONAPI\Document\ResourceCollection;
use JSONAPI\Exception\Document\DocumentException;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Helper\DoctrineProxyTrait;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\Pagination\UseTotalCount;
use JSONAPI\Uri\UriParser;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class DocumentBuilder
 *
 * @package JSONAPI
 */
class DocumentBuilder
{
    use DoctrineProxyTrait;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var Encoder
     */
    private Encoder $encoder;
    /**
     * @var Document
     */
    private Document $document;
    /**
     * @var LinkFactory
     */
    private LinkFactory $linkFactory;
    /**
     * @var InclusionFetcher
     */
    private InclusionFetcher $inclusionFetcher;

    private UriParser $uri;

    /**
     * DocumentBuilder constructor.
     *
     * @param Encoder              $encoder
     * @param InclusionFetcher     $inclusionFetcher
     * @param LinkFactory          $linkFactory
     * @param UriParser            $uri
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Encoder $encoder,
        InclusionFetcher $inclusionFetcher,
        LinkFactory $linkFactory,
        UriParser $uri,
        LoggerInterface $logger = null
    ) {
        $this->encoder          = $encoder;
        $this->inclusionFetcher = $inclusionFetcher;
        $this->linkFactory      = $linkFactory;
        $this->uri              = $uri;
        $this->logger           = $logger ?? new NullLogger();
        $this->document         = new Document();
    }

    /**
     * @param iterable<object>|object $data
     *
     * @return $this
     * @throws BadRequest
     * @throws DocumentException
     * @throws DriverException
     * @throws MetadataException
     */
    public function setData($data): DocumentBuilder
    {
        $this->logger->debug('Setting data.');
        if ($this->uri->getPath()->isCollection() && is_iterable($data)) {
            $this->logger->debug('It is resource collection.');
            $collection = new ResourceCollection();
            foreach ($data as $item) {
                if ($this->uri->getPath()->isRelationship()) {
                    $collection->add($this->encoder->getIdentifier($item));
                } else {
                    $collection->add($this->encoder->getResource($item));
                }
                if ($this->uri->getInclusion()->hasInclusions()) {
                    $this->inclusionFetcher->fetchInclusions($item, $this->uri->getInclusion()->getInclusions());
                }
            }
            $this->document->setData($collection);
        } elseif (is_object($data)) {
            $this->logger->debug('It is single resource');
            if ($this->uri->getPath()->isRelationship()) {
                $this->document->setData($this->encoder->getIdentifier($data));
            } else {
                $this->document->setData($this->encoder->getResource($data));
            }
            if ($this->uri->getInclusion()->hasInclusions()) {
                $this->inclusionFetcher->fetchInclusions($data, $this->uri->getInclusion()->getInclusions());
            }
        }
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
    public function setTotal(int $total): DocumentBuilder
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
        $this->linkFactory->setDocumentLinks($this->document, $this->uri);
        $this->logger->debug('Links set.');
        $this->document->setIncludes($this->inclusionFetcher->getIncluded());
        $this->logger->debug('Includes set.');
        $this->logger->debug('Built.');
        return $this->document;
    }
}
