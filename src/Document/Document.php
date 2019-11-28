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
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Exception\Http\NotFound;
use JSONAPI\Exception\Document\ResourceTypeMismatch;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Encoder\EncoderException;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\JsonDeserializable;
use JSONAPI\LinksTrait;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\MetaTrait;
use JSONAPI\Uri\Fieldset\FieldsetParser;
use JSONAPI\Uri\Fieldset\SortParser;
use JSONAPI\Uri\Filtering\VoidFilterParser;
use JSONAPI\Uri\Filter;
use JSONAPI\Uri\Inclusion\InclusionParser;
use JSONAPI\Uri\Pagination\LimitOffsetPaginationParser;
use JSONAPI\Uri\Pagination;
use JSONAPI\Uri\Path\PathParser;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
    private $factory;

    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Error[]
     */
    private $errors = [];

    /**
     * @var ResourceObject|ResourceObject[]|ResourceObjectIdentifier|ResourceObjectIdentifier[]|null
     */
    private $data;

    /**
     * @var ResourceObject[]
     */
    private $included = [];

    /**
     * Helper map of existing resources
     *
     * @var array
     */
    private $keymap = [];

    /**
     * @var ServerRequestInterface
     */
    private $request;
    /**
     * @var Filter
     */
    private $filterParser;
    /**
     * @var Pagination
     */
    private $paginationParser;
    /**
     * @var Filter
     */
    private $fieldsetParser;
    /**
     * @var InclusionParser
     */
    private $inclusionParser;
    /**
     * @var PathParser
     */
    private $pathParser;
    /**
     * @var SortParser
     */
    private $sortParser;

    /**
     * Document constructor.
     *
     * @param MetadataFactory        $metadataFactory
     * @param ServerRequestInterface $request
     * @param LoggerInterface|null   $logger
     *
     * @throws DocumentException
     * @throws DriverException
     * @throws EncoderException
     * @throws InvalidArgumentException
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        ServerRequestInterface $request,
        LoggerInterface $logger = null
    ) {
        $this->factory = $metadataFactory;
        $this->request = $request;
        $this->fieldsetParser = new FieldsetParser();
        $this->filterParser = new VoidFilterParser();
        $this->inclusionParser = new InclusionParser();
        $this->paginationParser = new LimitOffsetPaginationParser();
        $this->pathParser = new PathParser($metadataFactory);
        $this->sortParser = new SortParser();
        $this->logger = $logger ?? new NullLogger();
        $this->encoder = new Encoder($metadataFactory, $this->fieldsetParser, $this->logger);
        try {
            $this->parseUri();
            if (in_array(strtoupper($request->getMethod()), ['POST', 'PATCH'])) {
                $this->loadData();
            }
        } catch (BadRequest $exception) {
            $error = Error::fromException($exception);
            $this->addError($error);
        }
    }

    public function setFilterParser(Filter $parser)
    {
        $this->filterParser = $parser;
    }

    public function setPagination(Pagination $parser)
    {
        $this->paginationParser = $parser;
    }

    public function getFilter(): Filter
    {
        return $this->filterParser;
    }

    public function getPagination(): Pagination
    {
        return $this->paginationParser;
    }

    /**
     * @throws BadRequest
     * @throws InvalidArgumentException
     */
    private function parseUri()
    {
        $this->pathParser->parse($this->request->getUri()->getPath());
        $params = $this->request->getQueryParams();
        if (isset($params['fields'])) {
            $this->fieldsetParser->parse($params['fields']);
        }
        if (isset($params['filter'])) {
            $this->filterParser->parse($params['filter']);
        }
        if (isset($params['include'])) {
            $this->inclusionParser->parse($params['include']);
        }
        if (isset($params['page'])) {
            $this->paginationParser->parse($params['page']);
        }
        if (isset($params['sort'])) {
            $this->sortParser->parse($params['sort']);
        }
    }

    /**
     * @return void
     * @throws DocumentException
     * @throws DriverException
     * @throws EncoderException
     * @throws InvalidArgumentException
     */
    private function loadData(): void
    {
        $primaryDataType = $this->pathParser->getPrimaryDataType();
        $metadata = $this->factory->getMetadataClassByType($primaryDataType);
        $body = $this->request->getParsedBody();

        if (is_array($body->data)) {
            $this->data = [];
            foreach ($body->data as $object) {
                if ($object->type !== $primaryDataType) {
                    throw new ResourceTypeMismatch();
                }
                $resource = $this->getResourceObject($object, $metadata);
                $this->data[] = $resource;
            }
        } else {
            $this->data = null;
            if ($body->data->type !== $primaryDataType) {
                throw new ResourceTypeMismatch();
            }
            $this->data = $this->getResourceObject($body->data, $metadata);
        }
    }

    /**
     * @param               $object
     * @param ClassMetadata $metadata
     *
     * @return ResourceObject
     * @throws DriverException
     * @throws EncoderException
     * @throws InvalidArgumentException
     * @throws DocumentException
     */
    private function getResourceObject($object, ClassMetadata $metadata): ResourceObjectIdentifier
    {
        if ($object instanceof stdClass) {
            $resource = new ResourceObject(new ResourceObjectIdentifier($object->type, @$object->id));
            foreach ($object->attributes ?? [] as $attribute => $value) {
                $attr = $metadata->getAttribute($attribute);
                try {
                    $className = $attr->type;
                    if ((new ReflectionClass($className))->implementsInterface(JsonDeserializable::class)) {
                        /** @var JsonDeserializable $className */
                        $value = $className::jsonDeserialize($value);
                    }
                } catch (ReflectionException $ignored) {
                    //NOSONAR
                }
                $resource->addAttribute(new Attribute($attribute, $value));
            }

            foreach ($object->relationships ?? [] as $prop => $value) {
                $value = $value->data;
                if (is_array($value)) {
                    $data = new ArrayCollection();
                    foreach ($value as $item) {
                        $data->add(new ResourceObjectIdentifier($item->type, $item->id));
                    }
                } else {
                    $data = new ResourceObjectIdentifier($value->type, $value->id);
                }
                $relationship = new Relationship($prop, $data);
                $resource->addRelationship($relationship);
            }
            return $resource;
        } else {
            if ($this->pathParser->isRelationship()) {
                $resource = $this->encoder->identify($object);
            } else {
                $resource = $this->encoder->encode($object);
            }
            if ($resource->getType() !== $metadata->getResource()->type) {
                throw new ResourceTypeMismatch();
            }
            return $resource;
        }
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
    public function addError(Error $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return ResourceObject|ResourceObject[]|ResourceObjectIdentifier|ResourceObjectIdentifier[]|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param object|object[] $data
     *
     * @throws BadRequest
     * @throws DriverException
     * @throws EncoderException
     * @throws InvalidArgumentException
     */
    public function setData($data): void
    {
        if (count($this->errors) > 0) {
            return;
        }
        $dataType = $this->pathParser->getPrimaryDataType();
        $metadata = $this->factory->getMetadataClassByType($dataType);
        if ($this->pathParser->isCollection()) {
            if (!is_iterable($data)) {
                throw new InvalidArgumentException("Collection fetch was detected, but data are not array");
            }
            $this->data = [];
            foreach ($data as $object) {
                $resource = $this->getResourceObject($object, $metadata);
                $key = self::getKey($resource);
                if (!isset($this->keymap[$key])) {
                    $this->data[] = $this->getResourceObject($object, $metadata);
                    $this->setIncludes($this->inclusionParser->getIncludes(), $object);
                    $this->keymap[$key] = true;
                }
            }
        } else {
            if (is_null($data)) {
                if ($this->pathParser->getRelation()) {
                    $this->data = null;
                } else {
                    throw new NotFound();
                }
            } else {
                $resource = $this->getResourceObject($data, $metadata);
                $key = self::getKey($resource);
                $this->data = $resource;
                $this->setIncludes($this->inclusionParser->getIncludes(), $data);
                $this->keymap[$key] = true;
            }
        }
    }

    private static function getKey(ResourceObjectIdentifier $resource): string
    {
        return $resource->getType() . $resource->getId();
    }

    private function setPrimaryLink()
    {

    }

    /**
     * @param $includes
     * @param $object
     *
     * @throws DriverException
     * @throws EncoderException
     * @throws BadRequest
     * @throws InvalidArgumentException
     */
    private function setIncludes($includes, $object)
    {
        $metadata = $this->factory->getMetadataByClass(get_class($object));
        foreach ($includes ?? [] as $include => $sub) {
            if ($relationship = $metadata->getRelationship($include)) {
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
                                if ($sub) {
                                    $this->setIncludes($sub, $item);
                                }
                            }
                        }
                    } else {
                        $relation = $this->encoder->encode($data);
                        $key = self::getKey($relation);
                        if (!isset($this->keymap[$key])) {
                            $this->included[] = $relation;
                            $this->keymap[$key] = true;
                            if ($sub) {
                                $this->setIncludes($sub, $data);
                            }
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
        if ($this->links) {
            $ret["links"] = $this->links;
        }
        if ($this->meta) {
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
