<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace JSONAPI\Document;

use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Encoder\InvalidField;
use JSONAPI\Exception\Document\BadRequest;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Document\NotFound;
use JSONAPI\Exception\Document\ResourceTypeMismatch;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Query\LinkProvider;
use JSONAPI\Query\Query;
use JSONAPI\Query\QueryFactory;
use JSONAPI\Utils\LinksImpl;
use JSONAPI\Utils\MetaImpl;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Document
 *
 * @package JSONAPI\Document
 */
class Document implements JsonSerializable, HasLinks, HasMeta
{
    const MEDIA_TYPE = "application/vnd.api+json";
    const VERSION = "1.0";

    use LinksImpl;
    use MetaImpl;

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
    private $url;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Error[]
     */
    private $errors = [];

    /**
     * @var ResourceObject|ResourceObject[]
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
     * @param LoggerInterface|null $logger
     */
    public function __construct(MetadataFactory $metadataFactory, LoggerInterface $logger = null)
    {
        $this->factory = $metadataFactory;
        $this->logger = $logger ?? new NullLogger();
        $this->url = new Query();
        $this->encoder = new Encoder($metadataFactory, $this->url, $this->logger);
    }

    /**
     * @param ServerRequestInterface $request
     * @param MetadataFactory        $factory
     * @return Document
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws ResourceTypeMismatch
     */
    public static function createFromRequest(ServerRequestInterface $request, MetadataFactory $factory): Document
    {
        $document = new static($factory);
        $body = $request->getParsedBody();
        if (is_array($body->data)) {
            $document->data = [];
            foreach ($body->data as $resourceDto) {
                if ($resourceDto->type !== $document->getDataType()) {
                    throw new ResourceTypeMismatch();
                }
                $object = new ResourceObject(new ResourceObjectIdentifier($resourceDto->type, $resourceDto->id));
                foreach (@$resourceDto->attributes ?? [] as $attribute => $value) {
                    $object->addAttribute(new Attribute($attribute, $value));
                }

                foreach (@$resourceDto->relationships ?? [] as $prop => $value) {
                    $value = $value->data;
                    if (is_array($value)) {
                        $data = [];
                        foreach ($value as $item) {
                            $data[] = new ResourceObjectIdentifier($item->type, $item->id);
                        }
                    } else {
                        $data = new ResourceObjectIdentifier($value->type, $value->id);
                    }
                    $relationship = new Relationship($prop, $data);
                    $object->addRelationship($relationship);
                }
                $document->data[] = $object;
            }
        } else {
            $document->data = null;
            if ($body->data->type !== $document->getDataType()) {
                throw new ResourceTypeMismatch();
            }
            $object = new ResourceObject(new ResourceObjectIdentifier($body->data->type, @$body->data->id));
            foreach (@$body->data->attributes ?? [] as $attribute => $value) {
                $object->addAttribute(new Attribute($attribute, $value));
            }
            foreach (@$body->data->relationships ?? [] as $prop => $value) {
                $value = $value->data;
                if (is_array($value)) {
                    $data = [];
                    foreach ($value as $item) {
                        $data[] = new ResourceObjectIdentifier($item->type, $item->id);
                    }
                } else {
                    $data = new ResourceObjectIdentifier($value->type, $value->id);
                }
                $relationship = new Relationship($prop, $data);
                $object->addRelationship($relationship);
            }
            $document->data = $object;
        }

        return $document;
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
     * @return ResourceObject|ResourceObject[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param object|object[] $data
     * @throws AnnotationMisplace
     * @throws BadRequest
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     * @throws InvalidField
     * @throws NotFound
     * @throws ResourceTypeMismatch
     */
    public function setData($data): void
    {
        if ($this->isError) {
            return;
        }
        if ($this->isCollection() && !is_iterable($data)) {
            throw new InvalidArgumentException("Collection fetch was detected, but data are not array");
        }
        if (!$this->url->getPath()->isRelation() && empty($data)) {
            throw new NotFound();
        }

        $dataType = $this->getDataType();
        $metadata = $this->factory->getMetadataClassByType($dataType);
        $this->setLinks(LinkProvider::createPrimaryDataLinks());

        if ($this->isCollection()) {
            $this->data = [];
            foreach ($data as $obj) {
                $this->data[] = $this->save($obj, $metadata);
            }
        } else {
            $this->data = $this->save($data, $metadata);
        }
    }

    /**
     * Return ResourceObject or null if ResourceObject is already saved to data
     *
     * @param object        $object
     * @param ClassMetadata $metadata
     * @return ResourceObject|null
     * @throws AnnotationMisplace
     * @throws BadRequest
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     * @throws InvalidField
     * @throws ResourceTypeMismatch
     */
    private function save($object, ClassMetadata $metadata): ?ResourceObject
    {
        if ($object) {
            if ($this->url->getPath()->isRelationship()) {
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
                if (!$this->url->getPath()->isRelationship()) {
                    $this->setIncludes($this->url->getIncludes(), $object);
                }
                return $resource;
            }
        }
        return null;
    }

    /**
     * @param $id
     * @return bool
     */
    private function isUnique($id): bool
    {
        return !isset($this->ids[$id]);
    }

    /**
     * @param ResourceObject $resource
     * @return string
     */
    private function getId(ResourceObject $resource): string
    {
        return $resource->getType() . $resource->getId();
    }

    /**
     * @param $includes
     * @param $object
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @throws InvalidArgumentException
     * @throws InvalidField
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
     * @return string
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws InvalidArgumentException
     * @throws BadRequest
     */
    private function getDataType(): string
    {
        $metadata = $this->factory->getMetadataClassByType($this->url->getPath()->getResource());
        if ($name = $this->url->getPath()->getRelationshipName()) {
            return $this->factory->getMetadataByClass($metadata->getRelationship($name)->target)->getResource()->type;
        }
        return $metadata->getResource()->type;
    }

    /**
     * @return bool
     * @throws AnnotationMisplace
     * @throws BadRequest
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws InvalidArgumentException
     */
    private function isCollection(): bool
    {
        $metadata = $this->factory->getMetadataClassByType($this->url->getPath()->getResource());
        if ($name = $this->url->getPath()->getRelationshipName()) {
            return $metadata->getRelationship($name)->isCollection;
        }
        if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($this->url->getPath()->getId())) {
            return false;
        }
        return empty($this->url->getPath()->getId());
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
