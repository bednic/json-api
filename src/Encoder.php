<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 14:33
 */

namespace JSONAPI;

use Doctrine\Common\Util\ClassUtils;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Attribute;
use JSONAPI\Document\Fields;
use JSONAPI\Document\Resource;
use JSONAPI\Document\ResourceIdentifier;
use JSONAPI\Exception\DriverException;
use JSONAPI\Exception\EncoderException;
use JSONAPI\Exception\FactoryException;
use JSONAPI\Filter\Filter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;

/**
 * Class Encoder
 * @package JSONAPI
 */
class Encoder
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var LoggerInterface|NullLogger
     */
    private $logger;

    /**
     * @var object
     */
    private $object;

    /**
     * @var ReflectionClass
     */
    private $ref;

    /**
     * @var ClassMetadata
     */
    private $metadata = null;

    /**
     * Encoder constructor.
     * @param MetadataFactory $metadataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(MetadataFactory $metadataFactory, LoggerInterface $logger = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->logger = $logger ? $logger : new NullLogger();
    }

    /**
     * @param                     $object
     * @param EncoderOptions|null $options
     * @return ResourceIdentifier | Resource
     * @throws DriverException
     * @throws EncoderException
     * @throws FactoryException
     */
    public function encode($object, EncoderOptions $options = null): ResourceIdentifier
    {

        $options = $options ? $options : new EncoderOptions();
        $className = ClassUtils::getClass($object);
        $this->logger->debug("Init encoding of {$className}.");
        $encoder = clone $this;
        $encoder->object = $object;
        try {
            $encoder->ref = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new EncoderException("Class {$className} does not exist.");
        }
        $encoder->metadata = $this->metadataFactory->getMetadataByClass($className);
        $resource = new ResourceIdentifier($encoder->getType(), $encoder->getId());
        if ($options->isFullLinkage()) {
            $resource = new Resource($resource);
            if ($options->getFilter()) {
                $encoder->withFields($resource, $options->getFilter());
            }
        }
        return $resource;

    }

    public function decode(array $data): Resource
    {

        if(!isset($data["type"]) || empty($data["type"])) {
            throw new EncoderException("Resource type is not defined.");
        }
        $id = $data["id"]?$data["id"]:null;
        $type = $data["type"];
        $resourceIdentifier = new ResourceIdentifier($type, $data["id"]);
        $className = $this->metadataFactory->getClassByType($type);
        $metadata = $this->metadataFactory->getMetadataClassByType($type);
        $object = new $className();
        if(isset($data["attributes"]) && !empty($data["attributes"])){
            foreach ($data["attributes"] as $name => $value){
                if($attribute = $metadata->getAttribute($name)){
                    $object->${$attribute->property} = $value;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->metadata->getResource()->type;
    }

    /**
     * @return string|int|null
     */
    public function getId()
    {
        try {
            if ($this->metadata->getId()->getter != null) {
                return call_user_func([$this->object, $this->metadata->getId()->getter]);
            } else {
                return $this->ref->getProperty($this->metadata->getId()->property)->getValue($this->object);
            }
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * TODO: reduce complexity
     * @param array|null $filter
     * @return Encoder
     * @throws DriverException
     * @throws EncoderException
     * @throws FactoryException
     */
    private function withFields(Resource $resource, Filter $filter)
    {
        foreach (array_merge($this->metadata->getAttributes(), $this->metadata->getRelationships()) as $name => $field) {
            if ($filter && in_array($name, $filter) || !$filter) {
                $value = null;
                if ($field->getter != null) {
                    $value = call_user_func([$this->object, $field->getter]);
                } else {
                    try {
                        $value = $this->ref->getProperty($field->property)->getValue($this->object);
                    } catch (ReflectionException $ignored) {
                        //NOSONAR
                    }
                }
                if ($field instanceof Relationship) {
                    $relationship = new Document\Relationship($field->isCollection);
                    if (is_iterable($value)) {
                        foreach ($value as $object) {
                            $relationship->addResource($this->encode($object));
                        }
                    } else {
                        $relationship->addResource($value ? $this->encode($value) : null);
                    }
                    $relationship->setLinks(
                        LinkProvider::createRelationshipsLinks(
                            new ResourceIdentifier($this->getType(), $this->getId()),
                            $name
                        ));
                    $this->logger->debug("Adding relationship {$name}.");
                    $resource->addRelationship($relationship);
                } elseif ($field instanceof Attribute) {
                    if ($value instanceof \DateTime) {
                        // ISO 8601
                        $value = $value->format(DATE_ATOM);
                    }
                    $this->logger->debug("Adding attribute {$name}.");
                    $resource->addAttribute(new Document\Attribute($name, $value));
                } else {
                    throw new EncoderException("Field {$name} is not Attribute nor Relationship");
                }
            }
        }
        return $this;
    }

}
