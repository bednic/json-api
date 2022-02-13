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
use JSONAPI\Exception\Driver\UnexpectedAnnotationState;
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
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger    = $logger ? $logger : new NullLogger();
        $this->inflector = new EnglishInflector();
    }

    /**
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @return ClassMetadata
     * @throws DriverException
     * @throws MetadataException
     */
    public function getClassMetadata(string $className): ClassMetadata
    {
        try {
            $ref      = new ReflectionClass($className);
            $resource = $this->getResource($ref);
            if ($resource) {
                $this->logger->debug('Found resource ' . $ref->getShortName());
                if ($resource->type === null) {
                    $resource->type = $this->inflector->pluralize(
                        s($ref->getShortName())->snake()->replace('_', '-')->toString()
                    )[0];
                }
                $meta = $this->getMeta($ref);
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
        } catch (ReflectionException) {
            throw new ClassNotExist($className);
        }
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param Id|null         $id
     * @param Collection      $attributes
     * @param Collection      $relationships
     *
     * @throws BadSignature
     * @throws MethodNotExist
     * @throws UnexpectedAnnotationState
     */
    private function parseProperties(
        ReflectionClass $reflectionClass,
        ?Id &$id,
        Collection $attributes,
        Collection $relationships
    ): void {
        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            /** @var Id | null $id */
            if (!$id && $id = $this->getId($reflectionProperty)) {
                $id->property = $reflectionProperty->getName();
                $this->logger->debug('Found resource ID.');
            }
            /** @var Attribute | null $attribute */
            if ($attribute = $this->getAttribute($reflectionProperty)) {
                $attribute->property = $reflectionProperty->getName();
                $this->fillUpAttribute($attribute, $reflectionProperty, $reflectionClass);
                $attributes->push($attribute);
                $this->logger->debug('Found resource attribute ' . $attribute->name);
            }
            /** @var Relationship | null $relationship */
            if ($relationship = $this->getRelationship($reflectionProperty)) {
                $relationship->property = $reflectionProperty->getName();
                /** @var Meta | null $meta */
                if ($meta = $this->getMeta($reflectionProperty)) {
                    $relationship->meta = $meta;
                }
                $this->fillUpRelationship($relationship, $reflectionProperty, $reflectionClass);
                $relationships->push($relationship);
                $this->logger->debug('Found resource relationship ' . $relationship->name);
            }
        }
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param Id|null         $id
     * @param Collection      $attributes
     * @param Collection      $relationships
     *
     * @throws AnnotationMisplace
     * @throws BadSignature
     * @throws MethodNotExist
     * @throws UnexpectedAnnotationState
     */
    private function parseMethods(
        ReflectionClass $reflectionClass,
        ?Id &$id,
        Collection $attributes,
        Collection $relationships
    ): void {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (!$reflectionMethod->isConstructor() && !$reflectionMethod->isDestructor()) {
                /** @var Id|null $id */
                if (!$id && ($id = $this->getId($reflectionMethod))) {
                    $this->isGetter($reflectionMethod);
                    $id->getter = $reflectionMethod->getName();
                    $this->logger->debug('Found resource ID.');
                }
                /** @var Attribute|null $attribute */
                if ($attribute = $this->getAttribute($reflectionMethod)) {
                    $this->isGetter($reflectionMethod);
                    $attribute->getter = $reflectionMethod->getName();
                    $this->fillUpAttribute($attribute, $reflectionMethod, $reflectionClass);
                    $attributes->push($attribute);
                    $this->logger->debug('Found resource attribute ' . $attribute->name);
                }
                /** @var Relationship|null $relationship */
                if ($relationship = $this->getRelationship($reflectionMethod)) {
                    $this->isGetter($reflectionMethod);
                    $relationship->getter = $reflectionMethod->getName();
                    /** @var Meta | null $meta */
                    if ($meta = $this->getMeta($reflectionMethod)) {
                        $relationship->meta = $meta;
                    }
                    $this->fillUpRelationship($relationship, $reflectionMethod, $reflectionClass);
                    $relationships->push($relationship);
                    $this->logger->debug('Found resource relationship ' . $relationship->name);
                }
            }
        }
    }

    /**
     * @param ReflectionClass<object> $ref
     *
     * @return Resource|null
     * @throws UnexpectedAnnotationState
     */
    private function getResource(ReflectionClass $ref): ?Resource
    {
        $attributes = $ref->getAttributes(Resource::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) < 1) {
            return null;
        }
        if (count($attributes) > 1) {
            throw new UnexpectedAnnotationState("Expected 1 Resource annotation, got " . count($attributes));
        }
        /** @var Resource */
        return array_shift($attributes)->newInstance();
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty|ReflectionMethod $ref
     *
     * @return Meta|null
     * @throws UnexpectedAnnotationState
     */
    private function getMeta(ReflectionClass|ReflectionProperty|ReflectionMethod $ref): ?Meta
    {
        $attributes = $ref->getAttributes(Meta::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) < 1) {
            return null;
        }
        if (count($attributes) > 1) {
            throw new UnexpectedAnnotationState("Expected 1 Meta annotation, got " . count($attributes));
        }
        /** @var Meta */
        return array_shift($attributes)->newInstance();
    }

    /**
     * @param ReflectionProperty|ReflectionMethod $ref
     *
     * @return Id|null
     * @throws UnexpectedAnnotationState
     */
    private function getId(ReflectionProperty|ReflectionMethod $ref): ?Id
    {
        $attributes = $ref->getAttributes(Id::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) < 1) {
            return null;
        }
        if (count($attributes) > 1) {
            throw new UnexpectedAnnotationState("Expected 1 Id annotation, got " . count($attributes));
        }
        /** @var Id */
        return array_shift($attributes)->newInstance();
    }

    /**
     * @param ReflectionProperty|ReflectionMethod $ref
     *
     * @return Attribute|null
     * @throws UnexpectedAnnotationState
     */
    public function getAttribute(ReflectionProperty|ReflectionMethod $ref): ?Attribute
    {
        $attributes = $ref->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) < 1) {
            return null;
        }
        if (count($attributes) > 1) {
            throw new UnexpectedAnnotationState("Expected 1 Attribute annotation, got " . count($attributes));
        }
        /** @var Attribute */
        return array_shift($attributes)->newInstance();
    }

    /**
     * @param ReflectionProperty|ReflectionMethod $ref
     *
     * @return Relationship|null
     * @throws UnexpectedAnnotationState
     */
    public function getRelationship(ReflectionProperty|ReflectionMethod $ref): ?Relationship
    {
        $attributes = $ref->getAttributes(Relationship::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) < 1) {
            return null;
        }
        if (count($attributes) > 1) {
            throw new UnexpectedAnnotationState("Expected 1 Relationship annotation, got " . count($attributes));
        }
        /** @var Relationship */
        return array_shift($attributes)->newInstance();
    }
}
