<?php

declare(strict_types=1);

namespace JSONAPI\Driver;

use JSONAPI\Data\Collection;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\BadSignature;
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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

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
            $res = new ReflectionClass($className);
            if ($res->implementsInterface(Resource::class)) {
                $this->logger->debug("Found new resource class $className.");
                /** @var Resource $className */
                $classMetadata = $className::getSchema();
                $ref           = new ReflectionClass($classMetadata->getClassName());
                $attributes    = $this->parseAttributes($ref, $classMetadata->getAttributes());
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
        } catch (ReflectionException) {
            throw new ClassNotExist($className);
        }
    }

    /**
     * @param ReflectionClass<Resource> $reflectionClass
     * @param array<Attribute>          $metadata
     *
     * @return Collection<Attribute>
     * @throws MethodNotExist
     * @throws PropertyNotExist
     * @throws AnnotationMisplace
     * @throws BadSignature
     */
    private function parseAttributes(ReflectionClass $reflectionClass, iterable $metadata): Collection
    {
        $attributes = new Collection();
        /** @var Attribute $attribute */
        foreach ($metadata as $attribute) {
            $reflection = $this->getReflection($attribute, $reflectionClass);
            $this->fillUpAttribute($attribute, $reflection, $reflectionClass);
            $attributes->set($attribute->name, $attribute);
        }
        return $attributes;
    }

    /**
     * @param Field                     $metadata
     * @param ReflectionClass<Resource> $reflectionClass
     *
     * @return ReflectionMethod|ReflectionProperty
     * @throws AnnotationMisplace
     * @throws MethodNotExist
     * @throws PropertyNotExist
     */
    private function getReflection(
        Field $metadata,
        ReflectionClass $reflectionClass
    ): ReflectionMethod|ReflectionProperty {
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

    /**
     * @param ReflectionClass<Resource> $reflectionClass
     * @param array<Relationship>       $metadata
     *
     * @return Collection<Relationship>
     * @throws AnnotationMisplace
     * @throws MethodNotExist
     * @throws PropertyNotExist
     * @throws BadSignature
     */
    private function parseRelationships(ReflectionClass $reflectionClass, iterable $metadata): Collection
    {
        $relationships = new Collection();
        /** @var Relationship $relationship */
        foreach ($metadata as $relationship) {
            $reflection = $this->getReflection($relationship, $reflectionClass);
            $this->fillUpRelationship($relationship, $reflection, $reflectionClass);
            $relationships->set($relationship->name, $relationship);
        }
        return $relationships;
    }
}
