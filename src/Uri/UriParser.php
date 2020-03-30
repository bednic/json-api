<?php

declare(strict_types=1);

namespace JSONAPI\Uri;

use Fig\Http\Message\RequestMethodInterface;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Http\UnsupportedParameter;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Exception\Metadata\RelationNotFound;
use JSONAPI\Exception\MissingDependency;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Fieldset\FieldsetParser;
use JSONAPI\Uri\Fieldset\SortParser;
use JSONAPI\Uri\Filtering\Builder\DoctrineCriteriaExpressionBuilder;
use JSONAPI\Uri\Filtering\CriteriaFilterParser;
use JSONAPI\Uri\Filtering\ExpressionFilterParser;
use JSONAPI\Uri\Filtering\FilterInterface;
use JSONAPI\Uri\Filtering\FilterParserInterface;
use JSONAPI\Uri\Inclusion\InclusionInterface;
use JSONAPI\Uri\Inclusion\InclusionParser;
use JSONAPI\Uri\Pagination\LimitOffsetPagination;
use JSONAPI\Uri\Pagination\PaginationInterface;
use JSONAPI\Uri\Pagination\PaginationParserInterface;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Path\PathParser;
use JSONAPI\Uri\Sorting\SortInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class UriParser
 *
 * @package JSONAPI\Uri
 */
final class UriParser
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
    private ?MetadataRepository $metadataRepository = null;
    /**
     * Enables inclusion support
     *
     * @var bool
     */
    public static bool $inclusionEnabled = true;
    /**
     * Enables sort support
     *
     * @var bool
     */
    public static bool $sortEnabled = true;

    /**
     * UriParser constructor.
     *
     * @param ServerRequestInterface         $request
     * @param FilterParserInterface|null     $filterParser     Default is CriteriaFilterParser
     * @param PaginationParserInterface|null $paginationParser Default is LimitOffsetPagination
     * @param MetadataRepository|null        $metadataRepository
     * @param LoggerInterface|null           $logger
     *
     * @throws BadRequest
     */
    public function __construct(
        ServerRequestInterface $request,
        FilterParserInterface $filterParser = null,
        PaginationParserInterface $paginationParser = null,
        MetadataRepository $metadataRepository = null,
        LoggerInterface $logger = null
    ) {
        $this->check($request);
        $this->request = $request;
        $this->metadataRepository = $metadataRepository;
        $this->logger = $logger ?? new NullLogger();
        $this->fieldsetParser = new FieldsetParser();
        $this->filterParser = $filterParser ?? new ExpressionFilterParser();
        $this->inclusionParser = new InclusionParser();
        $this->paginationParser = $paginationParser ?? new LimitOffsetPagination();
        $this->pathParser = new PathParser();
        $this->sortParser = new SortParser();
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
        if (!self::$inclusionEnabled && in_array(UriPartInterface::INCLUSION_PART_KEY, $request->getQueryParams())) {
            throw new UnsupportedParameter(UriPartInterface::INCLUSION_PART_KEY);
        }
        if (!self::$sortEnabled && in_array(UriPartInterface::SORT_PART_KEY, $request->getQueryParams())) {
            throw new UnsupportedParameter(UriPartInterface::SORT_PART_KEY);
        }
    }

    /**
     * @param FilterParserInterface $parser
     *
     * @return UriParser
     */
    public function setFilterParser(FilterParserInterface $parser): self
    {
        $this->filterParser = $parser;
        return $this;
    }

    /**
     * @param PaginationParserInterface $parser
     *
     * @return UriParser
     */
    public function setPaginationParser(PaginationParserInterface $parser): self
    {
        $this->paginationParser = $parser;
        return $this;
    }

    /**
     * @param MetadataRepository $metadataRepository
     *
     * @return UriParser
     */
    public function setMetadataRepository(MetadataRepository $metadataRepository): self
    {
        $this->metadataRepository = $metadataRepository;
        return $this;
    }

    /**
     * @return FilterInterface
     * @throws BadRequest
     * @throws MetadataException
     * @throws MissingDependency
     */
    public function getFilter(): FilterInterface
    {
        $params = $this->request->getQueryParams()[UriPartInterface::FILTER_PART_KEY] ?? null;
        $classMetadata = $this->getMetadataRepository()->getByType($this->getPrimaryResourceType());
        $this->filterParser->setMetadata($classMetadata);
        return $this->filterParser->parse($params);
    }

    /**
     * @return PaginationInterface
     */
    public function getPagination(): PaginationInterface
    {
        $params = $this->request->getQueryParams()[UriPartInterface::PAGINATION_PART_KEY] ?? null;
        return $this->paginationParser->parse($params);
    }

    /**
     * @return SortInterface
     */
    public function getSort(): SortInterface
    {
        $params = $this->request->getQueryParams()[UriPartInterface::SORT_PART_KEY] ?? null;
        return $this->sortParser->parse($params);
    }

    /**
     * @return FieldsetInterface
     */
    public function getFieldset(): FieldsetInterface
    {
        $params = $this->request->getQueryParams()[UriPartInterface::FIELDS_PART_KEY] ?? null;
        return $this->fieldsetParser->parse($params);
    }

    /**
     * @return InclusionInterface
     */
    public function getInclusion(): InclusionInterface
    {
        $params = $this->request->getQueryParams()[UriPartInterface::INCLUSION_PART_KEY] ?? null;
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

    /**
     * @return bool
     * @throws BadRequest
     * @throws MissingDependency
     * @throws MetadataNotFound
     * @throws RelationNotFound
     * @uses \JSONAPI\Uri\UriParser::$metadataRepository
     *
     */
    public function isCollection(): bool
    {
        $path = $this->getPath();
        if ($path->getRelationshipName()) {
            return $this->getMetadataRepository()
                ->getByType($path->getResourceType())
                ->getRelationship($path->getRelationshipName())
                ->isCollection;
        }
        if ($path->getId()) {
            return false;
        }
        if (strtoupper($this->request->getMethod()) === RequestMethodInterface::METHOD_POST) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     * @throws BadRequest
     * @throws MetadataNotFound
     * @throws MissingDependency
     * @throws RelationNotFound
     */
    public function getPrimaryResourceType(): string
    {

        $path = $this->getPath();
        if ($path->getRelationshipName()) {
            return $this->getMetadataRepository()
                ->getByClass(
                    $this->getMetadataRepository()
                        ->getByType($path->getResourceType())
                        ->getRelationship($path->getRelationshipName())
                        ->target
                )
                ->getType();
        } else {
            return $this->getMetadataRepository()->getByType($path->getResourceType())->getType();
        }
    }

    /**
     * @return string|null
     * @throws BadRequest
     * @throws MetadataNotFound
     * @throws MissingDependency
     * @throws RelationNotFound
     */
    public function getRelationshipType(): ?string
    {
        $path = $this->getPath();
        if ($path->getRelationshipName()) {
            return $this->getMetadataRepository()
                ->getByClass(
                    $this->getMetadataRepository()
                        ->getByType($path->getResourceType())
                        ->getRelationship($path->getRelationshipName())
                        ->target
                )
                ->getType();
        }
        return null;
    }

    /**
     * @return MetadataRepository|null
     * @throws MissingDependency
     */
    private function getMetadataRepository(): MetadataRepository
    {
        if (is_null($this->metadataRepository)) {
            throw new MissingDependency("You have to set MetadataRepository first. See method ::setMetadata.");
        }
        return $this->metadataRepository;
    }
}
