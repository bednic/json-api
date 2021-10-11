<?php

declare(strict_types=1);

namespace JSONAPI\URI;

use JSONAPI\Configuration;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Http\UnsupportedParameter;
use JSONAPI\URI\Fieldset\FieldsetInterface;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Inclusion\InclusionInterface;
use JSONAPI\URI\Pagination\PaginationInterface;
use JSONAPI\URI\Path\PathInterface;
use JSONAPI\URI\Sorting\SortInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

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
     * @var Configuration configuration
     */
    private Configuration $configuration;


    /**
     * URIParser constructor.
     *
     * @param Configuration $configuration
     *
     */
    public function __construct(Configuration $configuration)
    {
        $this->logger        = $configuration->getLogger();
        $this->configuration = $configuration;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ParsedURI
     * @throws BadRequest
     */
    public function parse(ServerRequestInterface $request): ParsedURI
    {
        $this->check($request);
        $fieldset   = $this->configuration->getFieldsetParser()->parse(
            $request->getQueryParams()[QueryPartInterface::FIELDS_PART_KEY] ?? null
        );
        $filter     = $this->configuration->getFilterParser()->parse(
            $request->getQueryParams()[QueryPartInterface::FILTER_PART_KEY] ?? null
        );
        $inclusion  = $this->configuration->getInclusionParser()->parse(
            $request->getQueryParams()[QueryPartInterface::INCLUSION_PART_KEY] ?? null
        );
        $pagination = $this->configuration->getPaginationParser()->parse(
            $request->getQueryParams()[QueryPartInterface::PAGINATION_PART_KEY] ?? null
        );
        $path       = $this->configuration->getPathParser()->parse(
            $request->getUri()->getPath(),
            $request->getMethod()
        );
        $sort       = $this->configuration->getSortParser()->parse(
            $request->getQueryParams()[QueryPartInterface::SORT_PART_KEY] ?? null
        );
        return new class ($fieldset, $filter, $inclusion, $pagination, $path, $sort) implements ParsedURI {
            public function __construct(
                private FieldsetInterface $fieldset,
                private FilterInterface $filter,
                private InclusionInterface $inclusion,
                private PaginationInterface $pagination,
                private PathInterface $path,
                private SortInterface $sort
            ) {
            }

            /**
             * @return FieldsetInterface
             */
            public function getFieldset(): FieldsetInterface
            {
                return $this->fieldset;
            }

            /**
             * @return FilterInterface
             */
            public function getFilter(): FilterInterface
            {
                return $this->filter;
            }

            /**
             * @return InclusionInterface
             */
            public function getInclusion(): InclusionInterface
            {
                return $this->inclusion;
            }

            /**
             * @return PaginationInterface
             */
            public function getPagination(): PaginationInterface
            {
                return $this->pagination;
            }

            /**
             * @return PathInterface
             */
            public function getPath(): PathInterface
            {
                return $this->path;
            }

            /**
             * @return SortInterface
             */
            public function getSort(): SortInterface
            {
                return $this->sort;
            }
        };
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
            !$this->configuration->isSupportInclusion() && in_array(
                QueryPartInterface::INCLUSION_PART_KEY,
                $request->getQueryParams()
            )
        ) {
            throw new UnsupportedParameter(QueryPartInterface::INCLUSION_PART_KEY);
        }
        if (
            !$this->configuration->isSupportSort() && in_array(
                QueryPartInterface::SORT_PART_KEY,
                $request->getQueryParams()
            )
        ) {
            throw new UnsupportedParameter(QueryPartInterface::SORT_PART_KEY);
        }
        if (
            !$this->configuration->isSupportPagination() && in_array(
                QueryPartInterface::PAGINATION_PART_KEY,
                $request->getQueryParams()
            )
        ) {
            throw new UnsupportedParameter(QueryPartInterface::PAGINATION_PART_KEY);
        }
    }
}
