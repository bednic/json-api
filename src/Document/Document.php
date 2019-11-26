<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\Document\BadRequest;
use JSONAPI\Exception\Document\NotFound;
use JSONAPI\Exception\Document\ResourceTypeMismatch;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Encoder\EncoderException;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\JsonDeserializable;
use JSONAPI\LinksTrait;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\MetaTrait;
use JSONAPI\Query\LinkProvider;
use JSONAPI\Query\Path;
use JSONAPI\Query\Query;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;

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
     * @var Query
     */
    private $query;

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
     * Helper array
     *
     * @var array
     */
    private $ids = [];

    /**
     * @var bool
     */
    private $isError = false;

    /**
     * Document constructor.
     *
     * @param MetadataFactory      $metadataFactory
     * @param Query                $query
     * @param LoggerInterface|null $logger
     */
    public function __construct(MetadataFactory $metadataFactory, Query $query, LoggerInterface $logger = null)
    {
        $this->factory = $metadataFactory;
        $this->query = $query;
        $this->logger = $logger ?? new NullLogger();
        $this->encoder = new Encoder($metadataFactory, $query, $this->logger);
    }

    /**
     * @param ServerRequestInterface $request
     * @param MetadataFactory        $factory
     *
     * @return Document
     * @throws BadRequest
     * @throws DriverException
     */
    public static function createFromRequest(ServerRequestInterface $request, MetadataFactory $factory): Document
    {
        $query = new Query($request);
        $document = new static($factory, $query);
        $metadata = $factory->getMetadataClassByType($document->getDataType());
        $body = $request->getParsedBody();

        if (is_array($body->data)) {
            $document->data = [];
            foreach ($body->data as $resourceDto) {
                if ($resourceDto->type !== $document->getDataType()) {
                    throw new ResourceTypeMismatch();
                }
                $object = self::getResourceObject($resourceDto, $metadata);
                $document->data[] = $object;
            }
        } else {
            $document->data = null;
            if ($body->data->type !== $document->getDataType()) {
                throw new ResourceTypeMismatch();
            }
            $object = self::getResourceObject($body->data, $metadata);
            $document->data = $object;
        }
        return $document;
    }

    /**
     * @return string
     * @throws BadRequest
     * @throws DriverException
     * @throws InvalidArgumentException
     */
    private function getDataType(): string
    {
        $metadata = $this->factory->getMetadataClassByType($this->query->getPath()->getResource());
        if ($name = $this->query->getPath()->getRelationshipName()) {
            return $this->factory->getMetadataByClass($metadata->getRelationship($name)->target)->getResource()->type;
        }
        return $metadata->getResource()->type;
    }

    /**
     * @param               $resourceDto
     * @param ClassMetadata $metadata
     *
     * @return ResourceObject
     * @throws BadRequest
     */
    private static function getResourceObject($resourceDto, ClassMetadata $metadata)
    {
        $object = new ResourceObject(new ResourceObjectIdentifier($resourceDto->type, @$resourceDto->id));
        foreach ($resourceDto->attributes ?? [] as $attribute => $value) {
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
            $object->addAttribute(new Attribute($attribute, $value));
        }

        foreach ($resourceDto->relationships ?? [] as $prop => $value) {
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
            $object->addRelationship($relationship);
        }
        return $object;
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
        $this->isError = true;
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
        if ($this->isError) {
            return;
        }

        $dataType = $this->getDataType();
        $metadata = $this->factory->getMetadataClassByType($dataType);
        $this->setLinks(LinkProvider::createPrimaryDataLinks());

        if ($this->isCollection()) {
            if (!is_iterable($data)) {
                throw new InvalidArgumentException("Collection fetch was detected, but data are not array");
            }
            $this->data = [];
            foreach ($data as $obj) {
                $this->data[] = $this->save($obj, $metadata);
            }
        } else {
            if (!$this->query->getPath()->isRelation() && is_null($data)) {
                throw new NotFound();
            }
            $this->data = $this->save($data, $metadata);
        }
    }

    /**
     * @return bool
     * @throws BadRequest
     * @throws DriverException
     * @throws InvalidArgumentException
     */
    private function isCollection(): bool
    {
        $metadata = $this->factory->getMetadataClassByType($this->query->getPath()->getResource());
        if ($name = $this->query->getPath()->getRelationshipName()) {
            return $metadata->getRelationship($name)->isCollection;
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($this->query->getPath()->getId())) {
            return false;
        }
        return empty($this->query->getPath()->getId());
    }

    /**
     * Return ResourceObject or null if ResourceObject is already saved to data
     *
     * @param object        $object
     * @param ClassMetadata $metadata
     *
     * @return ResourceObjectIdentifier|null
     * @throws BadRequest
     * @throws DriverException
     * @throws EncoderException
     * @throws InvalidArgumentException
     */
    private function save($object, ClassMetadata $metadata): ?ResourceObjectIdentifier
    {
        if ($object) {
            if ($this->query->getPath()->isRelationship()) {
                $resource = $this->encoder->identify($object);
            } else {
                $resource = $this->encoder->encode($object);
            }

            if ($resource->getType() !== $metadata->getResource()->type) {
                throw new ResourceTypeMismatch();
            }

            $id = $this->getId($resource);
            if ($this->isUnique($id)) {
                $this->ids[$id] = true;
                if (!$this->query->getPath()->isRelationship()) {
                    $this->setIncludes($this->query->getIncludes(), $object);
                }
                return $resource;
            }
        }
        return null;
    }

    /**
     * @param ResourceObjectIdentifier $resource
     *
     * @return string
     */
    private function getId(ResourceObjectIdentifier $resource): string
    {
        return $resource->getType() . $resource->getId();
    }

    /**
     * @param $id
     *
     * @return bool
     */
    private function isUnique($id): bool
    {
        return !isset($this->ids[$id]);
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
                            $id = $this->getId($relation);
                            if ($this->isUnique($id)) {
                                $this->included[] = $relation;
                                $this->ids[$id] = true;
                                if ($sub) {
                                    $this->setIncludes($sub, $item);
                                }
                            }
                        }
                    } else {
                        $relation = $this->encoder->encode($data);
                        $id = $this->getId($relation);
                        if ($this->isUnique($id)) {
                            $this->included[] = $relation;
                            $this->ids[$id] = true;
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
        if ($this->isError) {
            $ret["errors"] = $this->errors;
        } else {
            $ret['data'] = $this->data;
        }
        if (count($this->included)) {
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
     * @return false|string
     */
    public function __toString(): string
    {
        return (string)json_encode($this);
    }
}
