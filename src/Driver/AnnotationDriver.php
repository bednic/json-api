<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 11:00
 */

namespace JSONAPI\Driver;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\BadMethodSignature;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Driver\MethodNotExist;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Id;
use JSONAPI\Metadata\Relationship;
use JSONAPI\Metadata\Resource;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

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
     * AnnotationDriver constructor.
     *
     * @param LoggerInterface|null $logger
     *
     * @throws DriverException
     */
    public function __construct(LoggerInterface $logger = null)
    {
        try {
            $this->logger = $logger ? $logger : new NullLogger();
            $this->reader = new AnnotationReader();
        } catch (AnnotationException $exception) {
            throw new DriverException($exception->getMessage(), 10, $exception);
        }
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
            /** @var Resource $resource */
            if ($resource = $this->reader->getClassAnnotation($ref, Resource::class)) {
                $this->logger->debug('Found resource ' . $resource->type);
                if ($resource->meta && !$ref->hasMethod($resource->meta->getter)) {
                    throw new MethodNotExist($resource->meta->getter, $ref->getName());
                }
                $id = null;
                $attributes = new ArrayCollection();
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
     * @param ReflectionClass  $reflectionClass
     * @param                  $id
     * @param ArrayCollection  $attributes
     * @param ArrayCollection  $relationships
     *
     * @throws MethodNotExist
     * @throws BadMethodSignature
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
     * @param ReflectionClass  $reflectionClass
     * @param                  $id
     * @param ArrayCollection  $attributes
     * @param ArrayCollection  $relationships
     *
     * @throws BadMethodSignature
     * @throws MethodNotExist
     * @throws AnnotationMisplace
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
