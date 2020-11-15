<?php

declare(strict_types=1);

namespace JSONAPI\URI;

use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Http\UnsupportedParameter;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use JSONAPI\URI\Fieldset\FieldsetParser;
use JSONAPI\URI\Filtering\ExpressionFilterParser;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Inclusion\InclusionInterface;
use JSONAPI\URI\Inclusion\InclusionParser;
use JSONAPI\URI\Pagination\LimitOffsetPagination;
use JSONAPI\URI\Pagination\PaginationInterface;
use JSONAPI\URI\Pagination\PaginationParserInterface;
use JSONAPI\URI\Path\PathInterface;
use JSONAPI\URI\Path\PathParser;
use JSONAPI\URI\Sorting\SortInterface;
use JSONAPI\URI\Sorting\SortParser;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class URIParser
 *
 * @package JSONAPI\URI
 */
final class URIParser
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;
    /**
     * @var FilterParserInterface
     */
    private FilterParserInterface $filterParser;
    /**
     * @var PaginationParserInterface
     */
    private PaginationParserInterface $paginationParser;
    /**
     * @var FieldsetParser
     */
    private FieldsetParser $fieldsetParser;
    /**
     * @var InclusionParser
     */
    private InclusionParser $inclusionParser;
    /**
     * @var PathParser
     */
    private PathParser $pathParser;
    /**
     * @var SortParser
     */
    private SortParser $sortParser;
    /**
     * @var MetadataRepository|null
     */
    private ?MetadataRepository $metadataRepository;

    private bool $supportInclusion;

    private bool $supportSort;

    private bool $supportPagination;


    /**
     * UriParser constructor.
     *
     * @param MetadataRepository             $metadataRepository
     * @param ServerRequestInterface         $request
     * @param string                         $baseUrl
     * @param FilterParserInterface|null     $filterParser     Default is CriteriaFilterParser
     * @param PaginationParserInterface|null $paginationParser Default is LimitOffsetPagination
     * @param bool                           $supportInclusion
     * @param bool                           $supportSort
     * @param bool                           $supportPagination
     * @param LoggerInterface|null           $logger
     *
     * @throws BadRequest
     */
    public function __construct(
        ServerRequestInterface $request,
        MetadataRepository $metadataRepository,
        string $baseUrl,
        bool $supportInclusion = true,
        bool $supportSort = true,
        bool $supportPagination = true,
        FilterParserInterface $filterParser = null,
        PaginationParserInterface $paginationParser = null,
        LoggerInterface $logger = null
    ) {
        $this->request            = $request;
        $this->metadataRepository = $metadataRepository;
        $this->logger             = $logger ?? new NullLogger();
        $this->fieldsetParser     = new FieldsetParser();
        $this->filterParser       = $filterParser ?? new ExpressionFilterParser();
        $this->inclusionParser    = new InclusionParser();
        $this->paginationParser   = $paginationParser ?? new LimitOffsetPagination();
        $this->pathParser         = new PathParser($metadataRepository, $baseUrl, $request->getMethod());
        $this->sortParser         = new SortParser();
        $this->supportInclusion   = $supportInclusion;
        $this->supportSort        = $supportSort;
        $this->supportPagination  = $supportPagination;
        $this->check($request);
    }

    /**
     * Checks if request is valid else throw bad request exception
     *
     * @param ServerRequestInterface $request
     *
     * @throws BadRequest
     */
    private function check(ServerRequestInterface $request)
    {
        $this->logger->debug('Checking allowed query parts.');
        if (
            !$this->supportInclusion && in_array(
                QueryPartInterface::INCLUSION_PART_KEY,
                $request->getQueryParams()
            )
        ) {
            throw new UnsupportedParameter(QueryPartInterface::INCLUSION_PART_KEY);
        }
        if (!$this->supportSort && in_array(QueryPartInterface::SORT_PART_KEY, $request->getQueryParams())) {
            throw new UnsupportedParameter(QueryPartInterface::SORT_PART_KEY);
        }
        if (
            !$this->supportPagination && in_array(
                QueryPartInterface::PAGINATION_PART_KEY,
                $request->getQueryParams()
            )
        ) {
            throw new UnsupportedParameter(QueryPartInterface::PAGINATION_PART_KEY);
        }
    }

    /**
     * @param FilterParserInterface $parser
     *
     * @return URIParser
     */
    public function setFilterParser(FilterParserInterface $parser): self
    {
        $this->filterParser = $parser;
        return $this;
    }

    /**
     * @param PaginationParserInterface $parser
     *
     * @return URIParser
     */
    public function setPaginationParser(PaginationParserInterface $parser): self
    {
        $this->paginationParser = $parser;
        return $this;
    }

    /**
     * @return FilterInterface
     * @throws BadRequest
     */
    public function getFilter(): FilterInterface
    {
        $params = $this->request->getQueryParams()[QueryPartInterface::FILTER_PART_KEY] ?? null;
        return $this->filterParser->parse($params);
    }

    /**
     * @return PaginationInterface
     */
    public function getPagination(): PaginationInterface
    {
        $params = $this->request->getQueryParams()[QueryPartInterface::PAGINATION_PART_KEY] ?? null;
        return $this->paginationParser->parse($params);
    }

    /**
     * @return SortInterface
     */
    public function getSort(): SortInterface
    {
        $params = $this->request->getQueryParams()[QueryPartInterface::SORT_PART_KEY] ?? null;
        return $this->sortParser->parse($params);
    }

    /**
     * @return FieldsetInterface
     */
    public function getFieldset(): FieldsetInterface
    {
        $params = $this->request->getQueryParams()[QueryPartInterface::FIELDS_PART_KEY] ?? null;
        return $this->fieldsetParser->parse($params);
    }

    /**
     * @return InclusionInterface
     */
    public function getInclusion(): InclusionInterface
    {
        $params = $this->request->getQueryParams()[QueryPartInterface::INCLUSION_PART_KEY] ?? null;
        return $this->inclusionParser->parse($params);
    }

    /**
     * @return PathInterface
     * @throws BadRequest
     */
    public function getPath(): PathInterface
    {
        return $this->pathParser->parse($this->request->getUri()->getPath());
    }
}
