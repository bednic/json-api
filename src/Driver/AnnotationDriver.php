<?php

declare(strict_types=1);

namespace JSONAPI\Driver;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Annotation\Attribute;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;
use JSONAPI\Annotation\Resource as ResourceAnnotation;
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
     * @var AnnotationReader
     */
    private AnnotationReader $reader;
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
        $this->logger = $logger ? $logger : new NullLogger();
        $this->reader = new AnnotationReader();
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
            /** @var ResourceAnnotation $resource */
            $resource = $this->reader->getClassAnnotation($ref, Resource::class);
            if ($resource) {
                $this->logger->debug('Found resource ' . $ref->getShortName());
                if ($resource->type === null) {
                    $resource->type = $this->inflector->pluralize(s($ref->getShortName())->camel()->toString())[0];
                }
                if ($resource->meta && !$ref->hasMethod($resource->meta->getter)) {
                    throw new MethodNotExist($resource->meta->getter, $ref->getName());
                }
                $id            = null;
                $attributes    = new ArrayCollection();
                $relationships = new ArrayCollection();
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
                    $resource->meta
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
     * @param ArrayCollection $attributes
     * @param ArrayCollection $relationships
     *
     * @throws BadSignature
     * @throws MethodNotExist
     */
    private function parseProperties(
        ReflectionClass $reflectionClass,
        ?Id &$id,
        ArrayCollection &$attributes,
        ArrayCollection &$relationships
    ): void {
        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            /** @var Id | null $id */
            if (!$id && $id = $this->reader->getPropertyAnnotation($reflectionProperty, Id::class)) {
                $id->property = $reflectionProperty->getName();
                $this->logger->debug('Found resource ID.');
            }
            /** @var Attribute | null $attribute */
            if ($attribute = $this->reader->getPropertyAnnotation($reflectionProperty, Attribute::class)) {
                $attribute->property = $reflectionProperty->getName();
                $this->fillUpAttribute($attribute, $reflectionProperty, $reflectionClass);
                $attributes->add($attribute);
                $this->logger->debug('Found resource attribute ' . $attribute->name);
            }
            /** @var Relationship | null $relationship */
            if ($relationship = $this->reader->getPropertyAnnotation($reflectionProperty, Relationship::class)) {
                $relationship->property = $reflectionProperty->getName();
                $this->fillUpRelationship($relationship, $reflectionProperty, $reflectionClass);
                $relationships->add($relationship);
                $this->logger->debug('Found resource relationship ' . $relationship->name);
            }
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param Id|null         $id
     * @param ArrayCollection $attributes
     * @param ArrayCollection $relationships
     *
     * @throws AnnotationMisplace
     * @throws BadSignature
     * @throws MethodNotExist
     */
    private function parseMethods(
        ReflectionClass $reflectionClass,
        ?Id &$id,
        ArrayCollection &$attributes,
        ArrayCollection &$relationships
    ): void {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (!$reflectionMethod->isConstructor() && !$reflectionMethod->isDestructor()) {
                /** @var Id $id */
                if (!$id && ($id = $this->reader->getMethodAnnotation($reflectionMethod, Id::class))) {
                    $this->isGetter($reflectionMethod);
                    $id->getter = $reflectionMethod->getName();
                    $this->logger->debug('Found resource ID.');
                }
                /** @var Attribute $attribute */
                if ($attribute = $this->reader->getMethodAnnotation($reflectionMethod, Attribute::class)) {
                    $this->isGetter($reflectionMethod);
                    $attribute->getter = $reflectionMethod->getName();
                    $this->fillUpAttribute($attribute, $reflectionMethod, $reflectionClass);
                    $attributes->add($attribute);
                    $this->logger->debug('Found resource attribute ' . $attribute->name);
                }
                /** @var Relationship $relationship */
                if ($relationship = $this->reader->getMethodAnnotation($reflectionMethod, Relationship::class)) {
                    $this->isGetter($reflectionMethod);
                    $relationship->getter = $reflectionMethod->getName();
                    $this->fillUpRelationship($relationship, $reflectionMethod, $reflectionClass);
                    $relationships->add($relationship);
                    $this->logger->debug('Found resource relationship ' . $relationship->name);
                }
            }
        }
    }
}
