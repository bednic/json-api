<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\DocumentException;
use JSONAPI\Exception\DriverException;
use JSONAPI\Exception\EncoderException;
use JSONAPI\Exception\FactoryException;
use JSONAPI\Exception\JsonApiException;
use JSONAPI\Exception\UnsupportedMediaType;
use JSONAPI\Metadata\Encoder;
use JSONAPI\Metadata\MetadataFactory;
use JSONAPI\Query\LinkProvider;
use JSONAPI\Query\Query;
use JSONAPI\Query\QueryFactory;
use JsonSerializable;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Document
 *
 * @package JSONAPI\Document
 */
class Document implements JsonSerializable
{
    const MEDIA_TYPE = "application/vnd.api+json";

    const VERSION = "1.0";
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
     * @var \JSONAPI\Document\Resource|\JSONAPI\Document\Resource[]
     */
    private $data;

    /**
     * @var Error[]|ArrayCollection
     */
    private $errors;

    /**
     * @var Meta[]|ArrayCollection
     */
    private $meta;

    /**
     * @var Link[]|ArrayCollection
     */
    private $links;

    /**
     * @var ArrayCollection
     */
    private $included;

    /**
     * @var array
     */
    private $ids;

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
        $this->encoder = new Encoder($metadataFactory, $this->logger);
        $this->url = QueryFactory::create();
        $this->links = new ArrayCollection();
        $this->meta = new ArrayCollection();
        $this->included = new ArrayCollection();
    }

    /**
     * @param RequestInterface $request
     * @param MetadataFactory  $factory
     * @return Document
     * @throws DocumentException
     * @throws UnsupportedMediaType
     */
    public static function createFromRequest(RequestInterface $request, MetadataFactory $factory): Document
    {
        $url = QueryFactory::create();
        $document = new static($factory);
        $body = (string)$request->getBody();
        $meta = $request->getHeader('Content-Type');
        if (in_array(Document::MEDIA_TYPE, $meta)) {
            $body = json_decode($body);
            if (is_array($body->data)) {
                $data = [];
                foreach ($body->data as $resourceDto) {
                    if ($resourceDto->type !== $url->path->getPrimaryDataType()) {
                        throw new DocumentException("Primary data type mismatch from type gathered from url.",
                            DocumentException::DOCUMENT_PRIMARY_DATA_TYPE_MISMATCH);
                    }

                    $resource = new Resource(new ResourceIdentifier($resourceDto->type, $resourceDto->id));
                    foreach ($resourceDto->attributes as $attribute => $value) {
                        $resource->addAttribute(new Attribute($attribute, $value));
                    }

                    foreach ($resourceDto->relationships as $prop => $value) {
                        $value = $value->data;
                        $relationship = new Relationship($prop, is_array($value));
                        if ($relationship->isCollection()) {
                            foreach ($value as $item) {
                                $relationship->addResource(new ResourceIdentifier($item->type, $item->id));
                            }
                        } else {
                            $relationship->addResource(new ResourceIdentifier($value->type, $value->id));
                        }
                        $resource->addRelationship($relationship);
                    }
                    $data[] = $resource;
                }
                $document->data = $data;
            } else {
                if ($body->data->type !== $url->path->getPrimaryDataType()) {
                    throw new DocumentException("Primary data type mismatch from type gathered from url.",
                        DocumentException::DOCUMENT_PRIMARY_DATA_TYPE_MISMATCH);
                }
                $resource = new Resource(new ResourceIdentifier($body->data->type, $body->data->id));
                foreach ($body->data->attributes as $attribute => $value) {
                    $resource->addAttribute(new Attribute($attribute, $value));
                }
                foreach ($body->data->relationships as $prop => $value) {
                    $value = $value->data;
                    $relationship = new Relationship($prop, is_array($value));
                    if ($relationship->isCollection()) {
                        foreach ($value as $item) {
                            $relationship->addResource(new ResourceIdentifier($item->type, $item->id));
                        }
                    } else {
                        $relationship->addResource(new ResourceIdentifier($value->type, $value->id));
                    }
                    $resource->addRelationship($relationship);
                }
                $document->data = $resource;
            }
        } else {
            throw new UnsupportedMediaType();
        }
        return $document;
    }

    /**
     * @return \JSONAPI\Document\Resource|\JSONAPI\Document\Resource[]|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @throws DocumentException
     */
    public function setData($data): void
    {
        if ($this->isError) {
            throw new DocumentException(
                "Non-valid document. Data AND Errors are set. Only Data XOR Errors are allowed",
                DocumentException::DOCUMENT_HAS_DATA_AND_ERRORS);
        }
        try {
            $primaryDataType = $this->url->path->getPrimaryDataType();
            if ($this->url->path->isCollection()) {
                $this->data = [];
            } else {
                $this->data = null;
            }
            $metadata = $this->factory->getMetadataClassByType($primaryDataType);
            if (!empty($data)) {
                if (is_array($data)) {

                    foreach ($data as $obj) {
                        $resource = $this->encoder->encode($obj);
                        if ($resource->getType() !== $metadata->getResource()->type) {
                            throw new DocumentException("Primary data type mismatch from type gathered from url.",
                                DocumentException::DOCUMENT_PRIMARY_DATA_TYPE_MISMATCH);
                        }

                        $id = $this->getId($resource);
                        if ($this->isUnique($id)) {
                            $this->data[] = $resource;
                            $this->ids[$id] = true;
                            $this->setIncludes($this->url->getIncludes(), $obj);
                        }
                    }
                } else {
                    $resource = $this->encoder->encode($data);
                    if ($resource->getType() !== $metadata->getResource()->type) {
                        throw new DocumentException("Primary data type mismatch from type gathered from url.",
                            DocumentException::DOCUMENT_PRIMARY_DATA_TYPE_MISMATCH);
                    }
                    $id = $this->getId($resource);
                    $this->ids[$id] = true;
                    $this->data = $resource;
                    $this->setIncludes($this->url->getIncludes(), $data);
                }

            }

            $link = new Link(...LinkProvider::createPrimaryDataLink());
            $this->addLink($link);
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
     * @param \JSONAPI\Document\Resource $resource
     * @return string
     */
    private function getId(\JSONAPI\Document\Resource $resource): string
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

        foreach ($includes as $include => $sub) {
            $relationship = $metadata->getRelationship($include);

            if ($relationship && $relationship->getter) {
                $data = call_user_func([$object, $relationship->getter]);
                if ($relationship->isCollection) {
                    foreach ($data as $item) {
                        $relation = $this->encoder->encode($item);
                        $id = $this->getId($relation);
                        if ($this->isUnique($id)) {
                            $this->included->add($relation);
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
                        $this->included->add($relation);
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
     * @param Link $link
     */
    public function addLink(Link $link): void
    {
        $this->links->set($link->getKey(), $link->getValue());
    }

    /**
     * @param Meta $meta
     */
    public function addMeta(Meta $meta)
    {
        $this->meta->set($meta->getKey(), $meta->getValue());
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
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @throws DocumentException
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $ret = [];
        $ret["jsonapi"] = ["version" => self::VERSION];
        if (!$this->meta->isEmpty()) {
            $ret["meta"] = $this->meta->toArray();
        }

        if ($this->isError) {
            $ret["errors"] = $this->errors;
        } else {
            $ret["data"] = $this->data;
        }

        if (!$this->links->isEmpty()) {
            $ret["links"] = $this->links->toArray();
        }
        if (!$this->included->isEmpty()) {
            $ret["included"] = $this->included->toArray();
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
