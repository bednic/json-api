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
use JSONAPI\Document\Fields;
use JSONAPI\Document\Resource;
use JSONAPI\Document\ResourceIdentifier;
use JSONAPI\Exception\EncoderException;
use JSONAPI\Exception\FactoryException;
use JSONAPI\Exception\NullException;
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
     * @var Fields
     */
    private $fields = null;

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


    public function create($object): Encoder
    {
        return $this->__invoke($object);
    }


    public function __invoke($object): Encoder
    {
        $that = new self($this->metadataFactory, $this->logger);
        // this is only necessary when using doctrine proxy
        $className = ClassUtils::getClass($object);
        $that->object = $object;
        $this->logger->debug("Init encoding of {$className}.");
        try {
            $that->ref = new ReflectionClass($className);
            $that->metadata = $this->metadataFactory->getMetadataByClass($className);
        } catch (ReflectionException $e) {
            throw new EncoderException("Class {$className} doesn't exists",0, $e);
        }
        catch (FactoryException $e) {
            // factory exception je vyhazovana jen pokud trida neexistuje, nebo annotace nejsou validni, je otazka,
            // zda takove exception nejsou uz tak dost sebevypoidajici a nestaci tedy je poslat dal.
            // Navic by bylo fajn udelat nejakou obecnou exception, ktera by se dala odchytavat v systemu za vsechny
            throw new EncoderException("Class metadata not found or are not valid????l.");
        }
        return $that;
    }

    /**
     * @param array $filter
     * @return Encoder
     * @throws NullException
     */
    public function withFields(array $filter = null): Encoder
    {
        try {
            $this->fields = new Fields();
            foreach (array_merge($this->metadata->getAttributes(), $this->metadata->getRelationships()) as $name => $field) {
                if ($filter && in_array($name, $filter) || !$filter) {
                    if ($field->getter != null) {
                        $value = call_user_func([$this->object, $field->getter]);
                    } else {
                        $value = $this->ref->getProperty($field->property)->getValue($this->object);
                    }
                    if ($field instanceof Relationship) {
                        $relationship = new Document\Relationship($field->isCollection);
                        if (is_iterable($value)) {
                            foreach ($value as $object) {
                                $relationship->addResource($this($object)->encode());
                            }
                        } else {
                            $relationship->addResource($value ? $this($value)->encode() : null);
                        }
                        $relationship->setLinks(
                            LinkProvider::createRelationshipsLinks(
                                new ResourceIdentifier($this->getType(), $this->getId()), $name));
                        $value = $relationship;
                        $this->logger->debug("Field {$name} is relationship");
                    }
                    if ($value instanceof \DateTime) {
                        // ISO 8601
                        $value = $value->format(DATE_ATOM);
                    }
                    $this->logger->debug("Adding field {$name}");
                    $this->fields->addField($name, $value);
                }
            }
        } catch (ReflectionException $e) {
            throw new NullException($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }

    /**
     * @return ResourceIdentifier | Resource
     */
    public function encode(): ResourceIdentifier
    {
        $type = $this->getType();
        $id = $this->getId();
        $identifier = new ResourceIdentifier($type, $id);
        $this->logger->debug("Encoding resource <{$type}>[{$id}]");
        if ($this->fields) {
            return new Resource($identifier, $this->fields);
        } else {
            return $identifier;
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
     * @return mixed|null
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

}
