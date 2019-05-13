<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace JSONAPI\Document;

use JSONAPI\Exception\DocumentException;
use JSONAPI\Exception\DriverException;
use JSONAPI\Exception\EncoderException;
use JSONAPI\Exception\FactoryException;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\Exception\QueryException;
use JSONAPI\Exception\UnsupportedMediaType;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Query\LinkProvider;
use JSONAPI\Query\Query;
use JSONAPI\Query\QueryFactory;
use JSONAPI\Utils\LinksImpl;
use JSONAPI\Utils\MetaImpl;
use JsonSerializable;
use Psr\Http\Message\RequestInterface;
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
     * @throws QueryException
     */
    public function __construct(MetadataFactory $metadataFactory, LoggerInterface $logger = null)
    {
        $this->factory = $metadataFactory;
        $this->logger = $logger ?? new NullLogger();
        $this->encoder = new Encoder($metadataFactory, $this->logger);
        $this->url = QueryFactory::create();
    }

    /**
     * @param RequestInterface $request
     * @param MetadataFactory  $factory
     * @return Document
     * @throws DocumentException
     * @throws QueryException
     * @throws UnsupportedMediaType
     */
    public static function createFromRequest(RequestInterface $request, MetadataFactory $factory): Document
    {
        $document = new static($factory);
        $body = (string)$request->getBody();
        $meta = $request->getHeader('Content-Type');
        if (in_array(Document::MEDIA_TYPE, $meta)) {
            $body = json_decode($body);
            if (is_array($body->data)) {
                $document->data = [];
                foreach ($body->data as $resourceDto) {
                    if ($resourceDto->type !== $document->getPrimaryDataType()) {
                        throw new DocumentException(
                            "Primary data type mismatch from type gathered from url.",
                            DocumentException::RESOURCE_TYPE_MISMATCH
                        );
                    }

                    $object = new ResourceObject(new ResourceObjectIdentifier($resourceDto->type, $resourceDto->id));
                    foreach ($resourceDto->attributes as $attribute => $value) {
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
                if ($body->data->type !== $document->getPrimaryDataType()) {
                    throw new DocumentException(
                        "Primary data type mismatch from type gathered from url.",
                        DocumentException::RESOURCE_TYPE_MISMATCH
                    );
                }
                $object = new ResourceObject(new ResourceObjectIdentifier($body->data->type, @$body->data->id));
                foreach ($body->data->attributes as $attribute => $value) {
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
        } else {
            throw new UnsupportedMediaType();
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
     * @throws DocumentException
     */
    public function setData($data): void
    {
        if ($this->isError) {
            throw new DocumentException(
                "Non-valid document. Data AND Errors are set. Only Data XOR Errors are allowed",
                DocumentException::HAS_DATA_AND_ERRORS
            );
        }
        try {
            $primaryDataType = $this->getPrimaryDataType();
            $metadata = $this->factory->getMetadataClassByType($primaryDataType);
            $this->addLink(LinkProvider::createPrimaryDataLink());
            if ($this->isCollection()) {
                $this->data = [];
                foreach ($data as $obj) {
                    $resource = $this->encoder->encode($obj);
                    if ($resource->getType() !== $metadata->getResource()->type) {
                        throw new DocumentException(
                            "Primary data type mismatch from type gathered from url.",
                            DocumentException::RESOURCE_TYPE_MISMATCH
                        );
                    }

                    $id = $this->getId($resource);
                    if ($this->isUnique($id)) {
                        $this->ids[$id] = true;
                        $this->data[] = $resource;
                        $this->setIncludes($this->url->getIncludes(), $obj);
                    }
                }
            } else {
                $this->data = null;
                $resource = $this->encoder->encode($data);
                if ($resource->getType() !== $metadata->getResource()->type) {
                    throw new DocumentException(
                        "Primary data type mismatch from type gathered from url.",
                        DocumentException::RESOURCE_TYPE_MISMATCH
                    );
                }
                $id = $this->getId($resource);
                $this->ids[$id] = true;
                $this->data = $resource;
                $this->setIncludes($this->url->getIncludes(), $data);
            }
        } catch (JsonApiException $exception) {
            $this->addError(Error::fromException($exception));
        }
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
     * @throws DocumentException
     * @throws DriverException
     * @throws EncoderException
     * @throws FactoryException
     */
    private function setIncludes($includes, $object)
    {
        $metadata = $this->factory->getMetadataByClass(get_class($object));
        foreach ($includes ?? [] as $include => $sub) {
            $relationship = $metadata->getRelationship($include);

            if ($relationship && $relationship->getter) {
                $data = call_user_func([$object, $relationship->getter]);
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


    /**
     * @return string
     * @throws DriverException
     * @throws FactoryException
     */
    private function getPrimaryDataType(): string
    {
        $metadata = $this->factory->getMetadataClassByType($this->url->path->getResource());
        if ($name = $this->url->path->getRelationshipName()) {
            return $this->factory->getMetadataByClass($metadata->getRelationship($name)->target)->getResource()->type;
        }
        return $metadata->getResource()->type;
    }

    /**
     * @return bool
     * @throws DriverException
     * @throws FactoryException
     */
    private function isCollection(): bool
    {
        $metadata = $this->factory->getMetadataClassByType($this->url->path->getResource());
        if ($name = $this->url->path->getRelationshipName()) {
            return $metadata->getRelationship($name)->isCollection;
        }
        return $this->url->path->getId() ? false : true;
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
