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
use Doctrine\Common\Collections\Collection;
use Exception;
use JSONAPI\Annotation\Attribute;
use JSONAPI\Annotation\Field;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\BadMethodSignature;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Driver\MethodNotExist;
use JSONAPI\Exception\Driver\ReservedWord;
use JSONAPI\Metadata\ClassMetadata;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

/**
 * Class AnnotationDriver
 *
 * @package JSONAPI\Driver
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * Regex patter for getters
     */
    private const GETTER = '/^(get|is|has)/';
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
                return new ClassMetadata($ref->getName(), $id, $resource, $attributes, $relationships);
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
     * @throws DriverException
     */
    private function parseProperties(
        ReflectionClass $reflectionClass,
        &$id,
        ArrayCollection &$attributes,
        ArrayCollection &$relationships
    ): void {
        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            /** @var Id | null $id */
            if (!$id && ($id = $this->reader->getPropertyAnnotation($reflectionProperty, Id::class))) {
                if (!$id->property) {
                    $id->property = $reflectionProperty->getName();
                }
                $this->logger->debug('Found resource ID.');
            }
            /** @var Attribute | null $attribute */
            if ($attribute = $this->reader->getPropertyAnnotation($reflectionProperty, Attribute::class)) {
                if (!$attribute->name) {
                    $attribute->name = $reflectionProperty->getName();
                }
                if (!$attribute->property) {
                    $attribute->property = $reflectionProperty->getName();
                }
                $attribute->type = $reflectionProperty->getType() ? $reflectionProperty->getType()->getName() : null;
                $attributes->set($attribute->name, $attribute);
                $this->logger->debug('Found resource attribute ' . $attribute->name);
            }
            /** @var Relationship | null $relationship */
            if ($relationship = $this->reader->getPropertyAnnotation($reflectionProperty, Relationship::class)) {
                if (!$relationship->name) {
                    $relationship->name = $reflectionProperty->getName();
                }
                if (!$relationship->property) {
                    $relationship->property = $reflectionProperty->getName();
                }
                if (!$relationship->isCollection) {
                    $relationship->isCollection = $this->isCollection($reflectionProperty);
                }
                if ($relationship->meta && !$reflectionClass->hasMethod($relationship->meta->getter)) {
                    throw new MethodNotExist($relationship->meta->getter, $reflectionClass->getName());
                }
                $relationships->set($relationship->name, $relationship);
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
     * @throws DriverException
     */
    private function parseMethods(
        ReflectionClass $reflectionClass,
        &$id,
        ArrayCollection &$attributes,
        ArrayCollection &$relationships
    ): void {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (!$reflectionMethod->isConstructor() && !$reflectionMethod->isDestructor()) {
                /** @var Id $id */
                if (!$id && ($id = $this->reader->getMethodAnnotation($reflectionMethod, Id::class))) {
                    if (!$this->isGetter($reflectionMethod)) {
                        throw new AnnotationMisplace(
                            Id::class,
                            $reflectionMethod->getName(),
                            $reflectionClass->name
                        );
                    }
                    $id->getter = $reflectionMethod->getName();
                    $this->logger->debug('Found resource ID.');
                }
                /** @var Attribute $attribute */
                if ($attribute = $this->reader->getMethodAnnotation($reflectionMethod, Attribute::class)) {
                    if (!$this->isGetter($reflectionMethod)) {
                        throw new AnnotationMisplace(
                            Attribute::class,
                            $reflectionMethod->getName(),
                            $reflectionClass->name
                        );
                    }
                    $attribute->getter = $reflectionMethod->getName();

                    if (!$attribute->name) {
                        $attribute->name = $this->getName($reflectionMethod);
                    }

                    if ($attribute->setter === null) {
                        $attribute->setter = $this->getSetter($reflectionClass, $attribute);
                    }

                    if ($attribute->type === null) {
                        if ($reflectionMethod->getReturnType() !== null) {
                            $attribute->type = $reflectionMethod->getReturnType()->getName();
                        } elseif ($attribute->setter) {
                            $attribute->type = $this->getSetterParameterType($reflectionClass, $attribute);
                        }
                    }
                    $this->checkReservedNames($attribute->name);
                    $attributes->set($attribute->name, $attribute);

                    $this->logger->debug('Found resource attribute ' . $attribute->name);
                }
                /** @var Relationship $relationship */
                if ($relationship = $this->reader->getMethodAnnotation($reflectionMethod, Relationship::class)) {
                    if (!$this->isGetter($reflectionMethod)) {
                        throw new AnnotationMisplace(
                            Relationship::class,
                            $reflectionMethod->getName(),
                            $reflectionClass->name
                        );
                    }
                    if ($relationship->meta && !$reflectionClass->hasMethod($relationship->meta->getter)) {
                        throw new MethodNotExist($relationship->meta->getter, $reflectionClass->getName());
                    }

                    $relationship->getter = $reflectionMethod->getName();

                    if (!$relationship->name) {
                        $relationship->name = $this->getName($reflectionMethod);
                    }

                    if ($relationship->setter === null) {
                        $relationship->setter = $this->getSetter($reflectionClass, $relationship);
                    }

                    if ($relationship->isCollection === null) {
                        $relationship->isCollection = $this->isCollection($reflectionMethod);
                    }
                    $this->checkReservedNames($relationship->name);
                    $relationships->set($relationship->name, $relationship);

                    $this->logger->debug('Found resource relationship ' . $relationship->name);
                }
            }
        }
    }

    /**
     * This method try determine, if method on class is getter.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    private function isGetter(ReflectionMethod $reflectionMethod): bool
    {
        return ($reflectionMethod->hasReturnType() && $reflectionMethod->getReturnType()->getName() !== 'void')
            || preg_match(self::GETTER, $reflectionMethod->getName());
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     *
     * @return string
     */
    private function getName(ReflectionMethod $reflectionMethod): string
    {
        return lcfirst(preg_replace(self::GETTER, '', $reflectionMethod->getName()));
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param Field           $annotation
     *
     * @return string|null
     */
    private function getSetter(ReflectionClass $reflectionClass, Field $annotation): ?string
    {
        if ($reflectionClass->hasMethod(preg_replace(self::GETTER, 'set', $annotation->getter))) {
            return preg_replace(self::GETTER, 'set', $annotation->getter);
        }
        return null;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param Attribute       $attribute
     *
     * @return string
     * @throws DriverException
     */
    private function getSetterParameterType(ReflectionClass $reflectionClass, Attribute $attribute): string
    {
        try {
            $method = $reflectionClass->getMethod($attribute->setter);
        } catch (Exception $e) {
            throw new BadMethodSignature($reflectionClass->getName(), $attribute->setter);
        }
        if ($method->getNumberOfRequiredParameters() > 1) {
            throw new BadMethodSignature($method->getName(), $reflectionClass->getName());
        }
        $parameters = $method->getParameters();
        $parameter = array_shift($parameters);
        return $parameter->getType()->getName();
    }

    /**
     * @param Reflector $reflection
     *
     * @return bool
     * @throws DriverException
     */
    private function isCollection(Reflector $reflection): bool
    {
        if ($reflection instanceof ReflectionProperty || $reflection instanceof ReflectionMethod) {
            $type = $reflection instanceof ReflectionMethod ? $reflection->getReturnType() : $reflection->getType();
            try {
                if (
                    ($type->isBuiltin() && $type->getName() === 'array')
                    || (new ReflectionClass($type->getName()))->implementsInterface(Collection::class)
                ) {
                    return true;
                }
                return false;
            } catch (Exception $exception) {
                throw new DriverException($exception->getMessage(), $exception->getCode(), $exception);
            }
        } else {
            throw new DriverException("Unrecognized reflection.");
        }
    }

    /**
     * @param string $name
     *
     * @throws DriverException
     */
    private function checkReservedNames(string $name): void
    {
        if (in_array(strtolower($name), ['type', 'id'])) {
            throw new ReservedWord();
        }
    }
}
