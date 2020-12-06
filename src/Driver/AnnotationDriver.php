<?php

declare(strict_types=1);

namespace JSONAPI\Driver;

use JSONAPI\Annotation\Attribute;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Meta;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;
use JSONAPI\Data\Collection;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\BadSignature;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Driver\MethodNotExist;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\ClassMetadata;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\String\Inflector\EnglishInflector;
use function Symfony\Component\String\s;

/**
 * Class AnnotationDriver
 *
 * @package JSONAPI\AnnotationDriver
 */
class AnnotationDriver extends Driver
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var EnglishInflector
     */
    private EnglishInflector $inflector;

    /**
     * AnnotationDriver constructor.
     *
     * @param LoggerInterface|null $logger
     *
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger    = $logger ? $logger : new NullLogger();
        $this->inflector = new EnglishInflector();
    }

    /**
     * Returns metadata for provided class name
     *
     * @param string $className
     *
     * @return ClassMetadata
     * @throws DriverException
     * @throws MetadataException
     */
    public function getClassMetadata(string $className): ClassMetadata
    {
        try {
            $ref = new ReflectionClass($className);

            /** @var Resource|null $resource */
            $resource = (@$ref->getAttributes(Resource::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance();
            if ($resource) {
                $this->logger->debug('Found resource ' . $ref->getShortName());
                if ($resource->type === null) {
                    $resource->type = $this->inflector->pluralize(s($ref->getShortName())->camel()->toString())[0];
                }
                /** @var Meta $meta */
                $meta = (@$ref->getAttributes(Meta::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance();
                if ($meta && !$ref->hasMethod($meta->getter)) {
                    throw new MethodNotExist($meta->getter, $ref->getName());
                }
                $id            = null;
                $attributes    = new Collection();
                $relationships = new Collection();
                $this->parseProperties($ref, $id, $attributes, $relationships);
                $this->parseMethods($ref, $id, $attributes, $relationships);
                $this->logger->debug('Created ClassMetadata for ' . $resource->type);
                return new ClassMetadata(
                    $ref->getName(),
                    $resource->type,
                    $id,
                    $attributes,
                    $relationships,
                    $resource->readOnly,
                    $meta
                );
            } else {
                throw new ClassNotResource($className);
            }
        } catch (ReflectionException $e) {
            throw new ClassNotExist($className);
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param Id|null         $id
     * @param Collection      $attributes
     * @param Collection      $relationships
     *
     * @throws BadSignature
     * @throws MethodNotExist
     */
    private function parseProperties(
        ReflectionClass $reflectionClass,
        ?Id &$id,
        Collection $attributes,
        Collection $relationships
    ): void {
        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            /** @var Id | null $id */
            if (!$id && $id = (@$reflectionProperty->getAttributes(Id::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance()) {
                $id->property = $reflectionProperty->getName();
                $this->logger->debug('Found resource ID.');
            }
            /** @var Attribute | null $attribute */
            if ($attribute = (@$reflectionProperty->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance()) {
                $attribute->property = $reflectionProperty->getName();
                $this->fillUpAttribute($attribute, $reflectionProperty, $reflectionClass);
                $attributes->push($attribute);
                $this->logger->debug('Found resource attribute ' . $attribute->name);
            }
            /** @var Relationship | null $relationship */
            if ($relationship = (@$reflectionProperty->getAttributes(Relationship::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance()) {
                $relationship->property = $reflectionProperty->getName();
                /** @var Meta | null $meta */
                if ($meta = (@$reflectionProperty->getAttributes(Meta::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance()) {
                    $relationship->meta = $meta;
                }
                $this->fillUpRelationship($relationship, $reflectionProperty, $reflectionClass);
                $relationships->push($relationship);
                $this->logger->debug('Found resource relationship ' . $relationship->name);
            }
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param Id|null         $id
     * @param Collection      $attributes
     * @param Collection      $relationships
     *
     * @throws AnnotationMisplace
     * @throws BadSignature
     * @throws MethodNotExist
     */
    private function parseMethods(
        ReflectionClass $reflectionClass,
        ?Id &$id,
        Collection $attributes,
        Collection $relationships
    ): void {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (!$reflectionMethod->isConstructor() && !$reflectionMethod->isDestructor()) {
                /** @var Id $id */
                if (!$id && ($id = (@$reflectionMethod->getAttributes(Id::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance())) {
                    $this->isGetter($reflectionMethod);
                    $id->getter = $reflectionMethod->getName();
                    $this->logger->debug('Found resource ID.');
                }
                /** @var Attribute $attribute */
                if ($attribute = (@$reflectionMethod->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance()) {
                    $this->isGetter($reflectionMethod);
                    $attribute->getter = $reflectionMethod->getName();
                    $this->fillUpAttribute($attribute, $reflectionMethod, $reflectionClass);
                    $attributes->push($attribute);
                    $this->logger->debug('Found resource attribute ' . $attribute->name);
                }
                /** @var Relationship $relationship */
                if ($relationship = (@$reflectionMethod->getAttributes(Relationship::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance()) {
                    $this->isGetter($reflectionMethod);
                    $relationship->getter = $reflectionMethod->getName();
                    /** @var Meta | null $meta */
                    if ($meta = (@$reflectionMethod->getAttributes(Meta::class, ReflectionAttribute::IS_INSTANCEOF)[0])?->newInstance()) {
                        $relationship->meta = $meta;
                    }
                    $this->fillUpRelationship($relationship, $reflectionMethod, $reflectionClass);
                    $relationships->push($relationship);
                    $this->logger->debug('Found resource relationship ' . $relationship->name);
                }
            }
        }
    }
}
