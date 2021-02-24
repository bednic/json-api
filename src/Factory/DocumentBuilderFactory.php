<?php

declare(strict_types=1);

namespace JSONAPI\Factory;

use JSONAPI\Document\Builder;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Pagination\PaginationParserInterface;
use JSONAPI\URI\URIParser;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class DocumentBuilderFactory
 *
 * @package JSONAPI\Factory
 */
class DocumentBuilderFactory
{
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var PaginationParserInterface|null
     */
    private ?PaginationParserInterface $paginationParser;
    /**
     * @var FilterParserInterface|null
     */
    private ?FilterParserInterface $filterParser;
    /**
     * @var string
     */
    private string $baseURL;
    /**
     * @var int
     */
    private int $maxIncludedItems;
    /**
     * @var int
     */
    private int $relationshipLimit;
    /**
     * @var bool
     */
    private bool $relationshipData;
    /**
     * @var bool
     */
    private bool $supportInclusion;
    /**
     * @var bool
     */
    private bool $supportSort;
    /**
     * @var bool
     */
    private bool $supportPagination;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * DocumentInstantiationFactory constructor.
     *
     * @param MetadataRepository             $metadataRepository
     * @param string                         $baseURL
     * @param int                            $maxIncludedItems
     * @param int                            $relationshipLimit
     * @param bool                           $relationshipData
     * @param bool                           $supportInclusion
     * @param bool                           $supportSort
     * @param bool                           $supportPagination
     * @param PaginationParserInterface|null $paginationParser
     * @param FilterParserInterface|null     $filterParser
     * @param LoggerInterface|null           $logger
     */
    public function __construct(
        MetadataRepository $metadataRepository,
        string $baseURL,
        int $maxIncludedItems = 625,
        int $relationshipLimit = 25,
        bool $relationshipData = true,
        bool $supportInclusion = true,
        bool $supportSort = true,
        bool $supportPagination = true,
        PaginationParserInterface $paginationParser = null,
        FilterParserInterface $filterParser = null,
        LoggerInterface $logger = null
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->paginationParser = $paginationParser;
        $this->filterParser = $filterParser;
        $this->baseURL = $baseURL;
        $this->maxIncludedItems = $maxIncludedItems;
        $this->relationshipLimit = $relationshipLimit;
        $this->relationshipData = $relationshipData;
        $this->supportInclusion = $supportInclusion;
        $this->supportSort = $supportSort;
        $this->supportPagination = $supportPagination;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Builder
     * @throws BadRequest
     */
    public function new(ServerRequestInterface $request): Builder
    {
        $linkFactory = new LinkComposer($this->baseURL);
        $uriParser = $this->uri($request);
        $encoder = new Encoder(
            $this->metadataRepository,
            $uriParser->getFieldset(),
            $uriParser->getInclusion(),
            $linkFactory,
            $this->relationshipData,
            $this->relationshipLimit,
            $this->logger
        );
        $collector = new InclusionCollector(
            $this->metadataRepository,
            $encoder,
            $this->maxIncludedItems,
            $this->logger
        );
        return new Builder($encoder, $collector, $linkFactory, $uriParser, $this->logger);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return URIParser
     * @throws BadRequest
     */
    public function uri(ServerRequestInterface $request): URIParser
    {
        return new URIParser(
            $request,
            $this->metadataRepository,
            $this->baseURL,
            $this->supportInclusion,
            $this->supportSort,
            $this->supportPagination,
            $this->filterParser,
            $this->paginationParser,
            $this->logger
        );
    }
}
