<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\Document\DocumentException;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Http\Conflict;
use JSONAPI\Exception\Http\NotFound;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\JsonDeserializable;
use JSONAPI\LinksTrait;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\MetaTrait;
use JSONAPI\Uri\Fieldset\FieldsetInterface;
use JSONAPI\Uri\Fieldset\FieldsetParser;
use JSONAPI\Uri\Fieldset\SortParser;
use JSONAPI\Uri\Filtering\CriteriaFilterParser;
use JSONAPI\Uri\Filtering\FilterInterface;
use JSONAPI\Uri\Filtering\FilterParserInterface;
use JSONAPI\Uri\Inclusion\Inclusion;
use JSONAPI\Uri\Inclusion\InclusionInterface;
use JSONAPI\Uri\Inclusion\InclusionParser;
use JSONAPI\Uri\LinkFactory;
use JSONAPI\Uri\Pagination\LimitOffsetPagination;
use JSONAPI\Uri\Pagination\PaginationInterface;
use JSONAPI\Uri\Pagination\PaginationParserInterface;
use JSONAPI\Uri\Path\PathInterface;
use JSONAPI\Uri\Path\PathParser;
use JSONAPI\Uri\Sorting\SortInterface;
use JSONAPI\Uri\UriPartInterface;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\InvalidArgumentException as CacheException;
use ReflectionClass;
use ReflectionException;
use stdClass;

/**
 * Class Document
 *
 * @package JSONAPI\Document
 */
class Document implements JsonSerializable, HasLinks, HasMeta
{
    use LinksTrait;
    use MetaTrait;

    public const MEDIA_TYPE = "application/vnd.api+json";
    public const VERSION = "1.0";

    /**
     * @var MetadataFactory
     */
    private MetadataFactory $metadataFactory;

    /**
     * @var Encoder
     */
    private Encoder $encoder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Error[]
     */
    private array $errors = [];

    /**
     * @var ResourceObject|ResourceObject[]|ResourceObjectIdentifier|ResourceObjectIdentifier[]|null
     */
    private $data;

    /**
     * @var ResourceObject[]
     */
    private array $included = [];

    /**
     * Helper map of existing resources
     *
     * @var array
     */
    private array $keymap = [];

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
     * @var LinkFactory
     */
    private LinkFactory $linkFactory;

    /**
     * Document constructor.
     *
     * @param MetadataFactory        $metadataFactory
     * @param ServerRequestInterface $request
     * @param LoggerInterface|null   $logger
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        ServerRequestInterface $request,
        LoggerInterface $logger = null
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->request = $request;
        $this->fieldsetParser = new FieldsetParser();
        $this->filterParser = new CriteriaFilterParser();
        $this->inclusionParser = new InclusionParser();
        $this->paginationParser = new LimitOffsetPagination();
        $this->pathParser = new PathParser($metadataFactory);
        $this->sortParser = new SortParser();
        $this->linkFactory = new LinkFactory();

        $this->logger = $logger ?? new NullLogger();
        $this->encoder = new Encoder($metadataFactory, $this->fieldsetParser, $this->linkFactory, $this->logger);
    }

    /**
     * @param object $object
     *
     * @throws BadRequest
     * @throws DocumentException
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     * @throws CacheException
     */
    public function setResource($object)
    {
        $this->data = null;
        if (count($this->errors) > 0) {
            return;
        }
        if (is_null($object)) {
            if (!$this->getPath()->isRelationship()) {
                throw new NotFound();
            }
        } else {
            $metadata = $this->metadataFactory->getMetadataClassByType($this->getPath()->getPrimaryResourceType());
            $resource = $this->getResourceObject($object, $metadata);
            $key = self::getKey($resource);
            $this->data = $resource;
            $this->keymap[$key] = true;
            $this->setIncludes($this->inclusionParser->getInclusions(), $object);
        }
    }

    /**
     * @param iterable $collection
     * @param int|null $total is used for pagination.
     *                        If offset-based pagination is used, then it's total of items.
     *                        If page-based pagination is used, then total is total count of pages.
     *
     * @throws BadRequest
     * @throws DocumentException
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     * @throws CacheException
     */
    public function setCollection(iterable $collection, int $total): void
    {
        $this->data = [];
        if (count($this->errors) > 0) {
            return;
        }
        $metadata = $this->metadataFactory->getMetadataClassByType($this->getPath()->getPrimaryResourceType());
        foreach ($collection as $object) {
            $resource = $this->getResourceObject($object, $metadata);
            $key = self::getKey($resource);
            if (!isset($this->keymap[$key])) {
                $this->data[] = $this->getResourceObject($object, $metadata);
                $this->keymap[$key] = true;
                $this->setIncludes($this->inclusionParser->getInclusions(), $object);
            }
        }
        $path = $this->getPath();
        $filter = $this->getFilter();
        $inclusion = $this->getInclusion();
        $fieldset = $this->getFieldset();
        $sort = $this->getSort();
        $pagination = $this->getPagination();
        $pagination->setTotal($total);
        $this->addLink($this->linkFactory->getDocumentLink(
            LinkFactory::SELF,
            $path,
            $filter,
            $inclusion,
            $fieldset,
            $pagination,
            $sort
        ));
        if ($first = $pagination->first()) {
            $this->addLink($this->linkFactory->getDocumentLink(
                LinkFactory::FIRST,
                $path,
                $filter,
                $inclusion,
                $fieldset,
                $first,
                $sort
            ));
        }
        if ($last = $pagination->last()) {
            $this->addLink($this->linkFactory->getDocumentLink(
                LinkFactory::LAST,
                $path,
                $filter,
                $inclusion,
                $fieldset,
                $last,
                $sort
            ));
        }
        if ($prev = $pagination->prev()) {
            $this->addLink($this->linkFactory->getDocumentLink(
                LinkFactory::PREV,
                $path,
                $filter,
                $inclusion,
                $fieldset,
                $prev,
                $sort
            ));
        }
        if ($next = $pagination->next()) {
            $this->addLink($this->linkFactory->getDocumentLink(
                LinkFactory::NEXT,
                $path,
                $filter,
                $inclusion,
                $fieldset,
                $next,
                $sort
            ));
        }
    }

    /**
     * @return ResourceObject|ResourceObject[]|ResourceObjectIdentifier|ResourceObjectIdentifier[]|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param FilterParserInterface $parser
     */
    public function setFilterParser(FilterParserInterface $parser): void
    {
        $this->filterParser = $parser;
    }

    /**
     * @param PaginationParserInterface $parser
     */
    public function setPaginationParser(PaginationParserInterface $parser): void
    {
        $this->paginationParser = $parser;
    }

    /**
     * @return FilterInterface
     * @throws BadRequest
     */
    public function getFilter(): FilterInterface
    {
        $data = $this->request->getQueryParams()[UriPartInterface::FILTER_PART_KEY] ?? [];
        return $this->filterParser->parse($data);
    }

    /**
     * @return PaginationInterface
     */
    public function getPagination(): PaginationInterface
    {
        $data = $this->request->getQueryParams()[UriPartInterface::PAGINATION_PART_KEY] ?? [];
        return $this->paginationParser->parse($data);
    }

    /**
     * @return SortInterface
     */
    public function getSort(): SortInterface
    {
        $data = $this->request->getQueryParams()[UriPartInterface::SORT_PART_KEY] ?? '';
        return $this->sortParser->parse($data);
    }

    /**
     * @return FieldsetInterface
     */
    public function getFieldset(): FieldsetInterface
    {
        $data = $this->request->getQueryParams()[UriPartInterface::FIELDS_PART_KEY] ?? [];
        return $this->fieldsetParser->parse($data);
    }

    /**
     * @return InclusionInterface
     */
    public function getInclusion(): InclusionInterface
    {
        $data = $this->request->getQueryParams()[UriPartInterface::INCLUSION_PART_KEY] ?? '';
        return $this->inclusionParser->parse($data);
    }

    /**
     * @return PathInterface
     * @throws BadRequest
     */
    public function getPath(): PathInterface
    {
        return $this->pathParser->parse($this->request->getUri()->getPath(), $this->request->getMethod());
    }

    /**
     * @return Encoder
     */
    public function getEncoder(): Encoder
    {
        return $this->encoder;
    }

    /**
     * @param Error $error
     */
    public function addError(Error $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @param mixed $body json decoded request body
     *
     * @return void
     * @throws BadRequest
     * @throws DocumentException
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    public function loadRequestData(stdClass $body): void
    {
        $path = $this->getPath();
        $metadata = $this->metadataFactory->getMetadataClassByType($path->getPrimaryResourceType());
        if ($path->isCollection()) {
            $this->data = [];
            foreach ($body->data as $object) {
                $resource = $this->jsonToResourceObject($object, $metadata);
                $this->data[] = $resource;
            }
        } else {
            $this->data = $body->data ? $this->jsonToResourceObject($body->data, $metadata) : null;
        }
    }

    /**
     * @param stdClass      $object
     * @param ClassMetadata $metadata
     *
     * @return ResourceObject
     * @throws BadRequest
     * @throws DocumentException
     * @throws MetadataException
     */
    private function jsonToResourceObject(stdClass $object, ClassMetadata $metadata): ResourceObject
    {
        $resource = new ResourceObject(new ResourceObjectIdentifier($object->type, @$object->id));
        if ($resource->getType() !== $metadata->getResource()->type) {
            throw new Conflict();
        }
        foreach ($object->attributes ?? [] as $name => $value) {
            $attribute = $metadata->getAttribute($name);
            try {
                $className = $attribute->type;
                if ((new ReflectionClass($className))->implementsInterface(JsonDeserializable::class)) {
                    /** @var JsonDeserializable $className */
                    $value = $className::jsonDeserialize($value);
                }
            } catch (ReflectionException $ignored) {
                //NOSONAR
            }
            $resource->addAttribute(new Attribute($name, $value));
        }

        foreach ($object->relationships ?? [] as $name => $value) {
            $relationship = $metadata->getRelationship($name);
            $value = $value->data;

            if ($relationship->isCollection) {
                $data = new ArrayCollection();
                foreach ($value as $item) {
                    $data->add(new ResourceObjectIdentifier($item->type, $item->id));
                }
            } else {
                $data = new ResourceObjectIdentifier($value->type, $value->id);
            }
            $resource->addRelationship(new Relationship($name, $data));
        }
        return $resource;
    }

    /**
     * @param               $object
     * @param ClassMetadata $metadata
     *
     * @return ResourceObject
     * @throws BadRequest
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     * @throws DocumentException
     */
    private function getResourceObject($object, ClassMetadata $metadata): ResourceObjectIdentifier
    {
        if ($this->getPath()->isRelationship()) {
            $resource = $this->encoder->identify($object);
        } else {
            $resource = $this->encoder->encode($object);
        }
        if ($resource->getType() !== $metadata->getResource()->type) {
            throw new Conflict();
        }
        return $resource;
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return string
     */
    private static function getKey(ResourceObjectIdentifier $resource): string
    {
        return $resource->getType() . $resource->getId();
    }

    /**
     * @param Inclusion[] $inclusions
     * @param             $object
     *
     * @throws BadRequest
     * @throws CacheException
     * @throws DocumentException
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws MetadataException
     */
    private function setIncludes(array $inclusions, $object)
    {
        $metadata = $this->metadataFactory->getMetadataByClass(get_class($object));
        foreach ($inclusions as $inclusion) {
            $relationship = $metadata->getRelationship($inclusion->getRelationName());
            $data = null;
            if ($relationship->property) {
                $data = $object->{$relationship->property};
            } elseif ($relationship->getter) {
                $data = call_user_func([$object, $relationship->getter]);
            }
            if (!empty($data)) {
                if ($relationship->isCollection) {
                    foreach ($data as $item) {
                        $relation = $this->encoder->encode($item);
                        $key = self::getKey($relation);
                        if (isset($key)) {
                            $this->included[] = $relation;
                            $this->keymap[$key] = true;
                            if ($inclusion->hasInclusions()) {
                                $this->setIncludes($inclusion->getInclusions(), $item);
                            }
                        }
                    }
                } else {
                    $relation = $this->encoder->encode($data);
                    $key = self::getKey($relation);
                    if (!isset($this->keymap[$key])) {
                        $this->included[] = $relation;
                        $this->keymap[$key] = true;
                        if ($inclusion->hasInclusions()) {
                            $this->setIncludes($inclusion->getInclusions(), $data);
                        }
                    }
                }
            }
        }
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $ret["jsonapi"] = ["version" => self::VERSION];
        if (count($this->errors) > 0) {
            $ret["errors"] = $this->errors;
        } else {
            $ret['data'] = $this->data;
        }
        if (count($this->included) > 0) {
            $ret["included"] = $this->included;
        }
        if ($this->hasLinks()) {
            $ret["links"] = $this->links;
        }
        if (!$this->getMeta()->isEmpty()) {
            $ret["meta"] = $this->meta;
        }
        return $ret;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)json_encode($this);
    }
}
