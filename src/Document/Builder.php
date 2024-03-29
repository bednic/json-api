<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Encoding\Encoder;
use JSONAPI\Exception\Document\DocumentException;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Helper\DoctrineProxyTrait;
use JSONAPI\URI\Pagination\UseTotalCount;
use JSONAPI\URI\ParsedURI;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Builder
 *
 * @package JSONAPI
 */
final class Builder
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
     * @var LinkComposer
     */
    private LinkComposer $linkFactory;
    /**
     * @var InclusionCollector
     */
    private InclusionCollector $inclusionFetcher;

    private ParsedURI $uri;

    /**
     * DocumentBuilder constructor.
     *
     * @param Encoder              $encoder
     * @param InclusionCollector   $inclusionFetcher
     * @param LinkComposer         $linkFactory
     * @param ParsedURI            $uri
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Encoder $encoder,
        InclusionCollector $inclusionFetcher,
        LinkComposer $linkFactory,
        ParsedURI $uri,
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
     * @param iterable<object>|object|null $data
     *
     * @return $this
     * @throws BadRequest
     * @throws DocumentException
     * @throws DriverException
     * @throws MetadataException
     */
    public function setData(object|iterable|null $data): Builder
    {
        $this->logger->debug('Setting data.');
        if ($this->uri->getPath()->isCollection() && is_iterable($data)) {
            $this->logger->debug('It is resource collection.');
            $collection = new ResourceCollection();
            foreach ($data as $item) {
                if ($this->uri->getPath()->isRelationship()) {
                    $collection->add($this->encoder->identify($item));
                } else {
                    $collection->add($this->encoder->encode($item));
                }
                if ($this->uri->getInclusion()->hasInclusions()) {
                    $this->inclusionFetcher->fetchInclusions($item, $this->uri->getInclusion()->getInclusions());
                }
            }
            $this->document->setData($collection);
        } elseif (is_object($data)) {
            $this->logger->debug('It is single resource');
            if ($this->uri->getPath()->isRelationship()) {
                $this->document->setData($this->encoder->identify($data));
            } else {
                $this->document->setData($this->encoder->encode($data));
            }
            if ($this->uri->getInclusion()->hasInclusions()) {
                $this->inclusionFetcher->fetchInclusions($data, $this->uri->getInclusion()->getInclusions());
            }
        } else {
            $this->document->setData(null);
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
    public function setTotal(int $total): Builder
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
        if ($this->uri->getInclusion()->hasInclusions()) {
            $this->document->setIncludes($this->inclusionFetcher->getIncluded());
        }
        $this->logger->debug('Includes set.');
        $this->logger->debug('Build complete.');
        return $this->document;
    }
}
