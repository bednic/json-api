<?php

declare(strict_types=1);

namespace JSONAPI\Driver;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\BadMethodSignature;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Driver\MethodNotExist;
use JSONAPI\Exception\Driver\PropertyNotExist;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Field;
use JSONAPI\Metadata\Relationship;
use JSONAPI\Schema\Resource;
use JSONAPI\Schema\ResourceSchema;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

/**
 * Class SchemaDriver
 *
 * @package JSONAPI\Schema
 */
class SchemaDriver extends Driver
{
    private LoggerInterface $logger;

    /**
     * SchemaDriver constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }


    /**
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
            if ($ref->implementsInterface(Resource::class)) {
                /** @var Resource $className */
                $classMetadata = $className::getSchema();
                $attributes = $this->parseAttributes($ref, $classMetadata->getAttributes());
                $relationships = $this->parseRelationships($ref, $classMetadata->getRelationships());
                return new ClassMetadata(
                    $classMetadata->getClassName(),
                    $classMetadata->getType(),
                    $classMetadata->getId(),
                    $attributes,
                    $relationships,
                    $classMetadata->isReadOnly(),
                    $classMetadata->getMeta()
                );
            }
            throw new ClassNotResource($className);
        } catch (ReflectionException $reflectionException) {
            throw new ClassNotExist($className);
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param array           $metadata
     *
     * @return ArrayCollection
     * @throws MethodNotExist
     * @throws PropertyNotExist
     * @throws AnnotationMisplace
     * @throws BadMethodSignature
     */
    private function parseAttributes(ReflectionClass $reflectionClass, iterable $metadata): ArrayCollection
    {
        $attributes = new ArrayCollection();
        /** @var Attribute $attribute */
        foreach ($metadata as $attribute) {
            $reflection = $this->getReflection($attribute, $reflectionClass);
            $this->fillUpAttribute($attribute, $reflection, $reflectionClass);
            $attributes->set($attribute->name, $attribute);
        }
        return $attributes;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param array           $metadata
     *
     * @return ArrayCollection
     * @throws AnnotationMisplace
     * @throws MethodNotExist
     * @throws PropertyNotExist
     */
    private function parseRelationships(ReflectionClass $reflectionClass, iterable $metadata): ArrayCollection
    {
        $relationships = new ArrayCollection();
        /** @var Relationship $relationship */
        foreach ($metadata as $relationship) {
            $reflection = $this->getReflection($relationship, $reflectionClass);
            $this->fillUpRelationship($relationship, $reflection, $reflectionClass);
            $relationships->set($relationship->name, $relationship);
        }
        return $relationships;
    }

    /**
     * @param Field           $metadata
     * @param ReflectionClass $reflectionClass
     *
     * @return ReflectionMethod|ReflectionProperty
     * @throws AnnotationMisplace
     * @throws MethodNotExist
     * @throws PropertyNotExist
     */
    private function getReflection(Field $metadata, ReflectionClass $reflectionClass): Reflector
    {
        if ($metadata->property) {
            try {
                $reflection = $reflectionClass->getProperty($metadata->property);
            } catch (ReflectionException $exception) {
                throw new PropertyNotExist($metadata->property, $reflectionClass->getName());
            }
        } else {
            try {
                $reflection = $reflectionClass->getMethod($metadata->getter);
            } catch (ReflectionException $exception) {
                throw new MethodNotExist($metadata->getter, $reflectionClass->getName());
            }
            $this->isGetter($reflection);
        }
        return $reflection;
    }
}
