<?php

/**
 * Created by lasicka@logio.cz
 * at 06.10.2021 12:55
 */

declare(strict_types=1);

namespace JSONAPI;

use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\InvalidConfigurationParameter;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Fieldset\FieldsetParser;
use JSONAPI\URI\Fieldset\FieldsetParserInterface;
use JSONAPI\URI\Filtering\ExpressionFilterParser;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Inclusion\InclusionParser;
use JSONAPI\URI\Inclusion\InclusionParserInterface;
use JSONAPI\URI\Pagination\LimitOffsetPagination;
use JSONAPI\URI\Pagination\PaginationParserInterface;
use JSONAPI\URI\Path\PathParser;
use JSONAPI\URI\Path\PathParserInterface;
use JSONAPI\URI\Sorting\SortParser;
use JSONAPI\URI\Sorting\SortParserInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Configuration
 *
 * @package JSONAPI
 */
class Configuration
{
    /**
     * @var MetadataRepository metadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var string baseURL
     */
    private string $baseURL;
    /**
     * @var int maxIncludedItems
     */
    private int $maxIncludedItems;
    /**
     * @var int relationshipLimit
     */
    private int $relationshipLimit;
    /**
     * @var bool relationshipData
     */
    private bool $relationshipData;
    /**
     * @var bool supportInclusion
     */
    private bool $supportInclusion;
    /**
     * @var bool supportSort
     */
    private bool $supportSort;
    /**
     * @var bool supportPagination
     */
    private bool $supportPagination;
    /**
     * @var FieldsetParserInterface fieldsetParser
     */
    private FieldsetParserInterface $fieldsetParser;
    /**
     * @var FilterParserInterface filterParser
     */
    private FilterParserInterface $filterParser;
    /**
     * @var InclusionParserInterface inclusionParser
     */
    private InclusionParserInterface $inclusionParser;
    /**
     * @var PaginationParserInterface paginationParser
     */
    private PaginationParserInterface $paginationParser;
    /**
     * @var PathParserInterface pathParser
     */
    private PathParserInterface $pathParser;
    /**
     * @var SortParserInterface sortParser
     */
    private SortParserInterface $sortParser;
    /**
     * @var LoggerInterface logger
     */
    private LoggerInterface $logger;

    /**
     * Configuration constructor.
     *
     * @param MetadataRepository             $metadataRepository
     * @param string                         $baseURL
     * @param int                            $maxIncludedItems
     * @param int                            $relationshipLimit
     * @param bool                           $relationshipData
     * @param bool                           $supportInclusion
     * @param bool                           $supportSort
     * @param bool                           $supportPagination
     * @param FieldsetParserInterface|null   $fieldsetParser
     * @param FilterParserInterface|null     $filterParser
     * @param InclusionParserInterface|null  $inclusionParser
     * @param PaginationParserInterface|null $paginationParser
     * @param PathParserInterface|null       $pathParser
     * @param SortParserInterface|null       $sortParser
     * @param LoggerInterface|null           $logger
     *
     * @throws InvalidConfigurationParameter
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
        FieldsetParserInterface $fieldsetParser = null,
        FilterParserInterface $filterParser = null,
        InclusionParserInterface $inclusionParser = null,
        PaginationParserInterface $paginationParser = null,
        PathParserInterface $pathParser = null,
        SortParserInterface $sortParser = null,
        LoggerInterface $logger = null
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->setBaseURL($baseURL);
        $this->setMaxIncludedItems($maxIncludedItems);
        $this->setRelationshipLimit($relationshipLimit);
        $this->relationshipData  = $relationshipData;
        $this->supportInclusion  = $supportInclusion;
        $this->supportSort       = $supportSort;
        $this->supportPagination = $supportPagination;
        $this->fieldsetParser    = $fieldsetParser ?? new FieldsetParser();
        $this->filterParser      = $filterParser ?? new ExpressionFilterParser();
        $this->inclusionParser   = $inclusionParser ?? new InclusionParser();
        $this->paginationParser  = $paginationParser ?? new LimitOffsetPagination();
        $this->pathParser        = $pathParser ?? new PathParser($metadataRepository, $baseURL);
        $this->sortParser        = $sortParser ?? new SortParser();
        $this->logger            = $logger ?? new NullLogger();
    }

    /**
     * @param string $baseURL
     *
     * @throws InvalidConfigurationParameter
     */
    private function setBaseURL(string $baseURL): void
    {
        if (filter_var($baseURL, FILTER_VALIDATE_URL) === false) {
            throw new InvalidConfigurationParameter('baseURL', 'valid URL', $baseURL);
        }
        $this->baseURL = $baseURL;
    }

    /**
     * @param int $maxIncludedItems
     *
     * @throws InvalidConfigurationParameter
     */
    private function setMaxIncludedItems(int $maxIncludedItems): void
    {
        if ($maxIncludedItems < 0) {
            throw new InvalidConfigurationParameter('maxIncludedItems', 'value greater then 0', $maxIncludedItems);
        }
        $this->maxIncludedItems = $maxIncludedItems;
    }

    /**
     * @return MetadataRepository
     */
    public function getMetadataRepository(): MetadataRepository
    {
        return $this->metadataRepository;
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @return int
     */
    public function getMaxIncludedItems(): int
    {
        return $this->maxIncludedItems;
    }

    /**
     * @return int
     */
    public function getRelationshipLimit(): int
    {
        return $this->relationshipLimit;
    }

    /**
     * @param int $relationshipLimit
     *
     * @throws InvalidConfigurationParameter
     */
    public function setRelationshipLimit(int $relationshipLimit): void
    {
        if ($relationshipLimit < 0) {
            throw new InvalidConfigurationParameter('relationshipLimit', 'value greater then 0', $relationshipLimit);
        }
        $this->relationshipLimit = $relationshipLimit;
    }

    /**
     * @return bool
     */
    public function isRelationshipData(): bool
    {
        return $this->relationshipData;
    }

    /**
     * @return bool
     */
    public function isSupportInclusion(): bool
    {
        return $this->supportInclusion;
    }

    /**
     * @return bool
     */
    public function isSupportSort(): bool
    {
        return $this->supportSort;
    }

    /**
     * @return bool
     */
    public function isSupportPagination(): bool
    {
        return $this->supportPagination;
    }

    /**
     * @return FieldsetParserInterface
     */
    public function getFieldsetParser(): FieldsetParserInterface
    {
        return $this->fieldsetParser;
    }

    /**
     * @return FilterParserInterface
     */
    public function getFilterParser(): FilterParserInterface
    {
        return $this->filterParser;
    }

    /**
     * @return InclusionParserInterface
     */
    public function getInclusionParser(): InclusionParserInterface
    {
        return $this->inclusionParser;
    }

    /**
     * @return PaginationParserInterface
     */
    public function getPaginationParser(): PaginationParserInterface
    {
        return $this->paginationParser;
    }

    /**
     * @return PathParserInterface
     */
    public function getPathParser(): PathParserInterface
    {
        return $this->pathParser;
    }

    /**
     * @return SortParserInterface
     */
    public function getSortParser(): SortParserInterface
    {
        return $this->sortParser;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
