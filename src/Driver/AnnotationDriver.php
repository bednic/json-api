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
use JSONAPI\Annotation\Attribute;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\JsonDeserializable;
use JSONAPI\Metadata\ClassMetadata;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Traversable;

/**
 * Class AnnotationDriver
 *
 * @package JSONAPI\Driver
 */
class AnnotationDriver
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * Regex patter for getters
     */
    private const GETTER = '/^(is|get)/';

    /**
     * AnnotationDriver constructor.
     *
     * @param LoggerInterface|null $logger
     * @throws AnnotationException
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ? $logger : new NullLogger();
        $this->reader = new AnnotationReader();
    }

    /**
     * Returns metadata for provided class name
     *
     * @param string $className
     * @return ClassMetadata
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     */
    public function getClassMetadata(string $className): ClassMetadata
    {
        try {
            $ref = new ReflectionClass($className);
            /** @var Resource $resource */
            if ($resource = $this->reader->getClassAnnotation($ref, Resource::class)) {
                $this->logger->debug("Found resource {$resource->type}.");
                $id = null;
                $attributes = new ArrayCollection();
                $relationships = new ArrayCollection();
                $this->parseProperties($ref, $id, $attributes, $relationships);
                $this->parseMethods($ref, $id, $attributes, $relationships);
                $this->logger->info("Created ClassMetadata for <{$resource->type}>");
                return new ClassMetadata($className, $id, $resource, $attributes, $relationships);
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
     * @param                  $attributes
     * @param                  $relationships
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
                $this->logger->debug("Found resource ID.");
            }
            /** @var Attribute | null $attribute */
            if ($attribute = $this->reader->getPropertyAnnotation($reflectionProperty, Attribute::class)) {
                if (!$attribute->name) {
                    $attribute->name = $reflectionProperty->getName();
                }
                if (!$attribute->property) {
                    $attribute->property = $reflectionProperty->getName();
                }
                preg_match('/@var (?P<type>[a-zA-Z_-]+)/', $reflectionProperty->getDocComment(), $match);
                $attribute->type = $match['type'] ? $match['type'] : null;
                $attributes->set($attribute->name, $attribute);
                $this->logger->debug("Found resource attribute {$attribute->name}.");
            }
            /** @var Relationship | null $relationship */
            if ($relationship = $this->reader->getPropertyAnnotation($reflectionProperty, Relationship::class)) {
                if (!$relationship->name) {
                    $relationship->name = $reflectionProperty->getName();
                }
                if (!$relationship->property) {
                    $relationship->property = $reflectionProperty->getName();
                }
                $relationships->set($relationship->name, $relationship);
                $this->logger->debug("Found resource relationship {$relationship->name}.");
            }
        }
    }

    /**
     * @param ReflectionClass  $reflectionClass
     * @param                  $id
     * @param ArrayCollection  $attributes
     * @param ArrayCollection  $relationships
     * @throws AnnotationMisplace
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
                if (!$id && ($id = $this->reader->getMethodAnnotation($reflectionMethod, Id::class))) {
                    if (!$id->getter && !$this->isGetter($reflectionMethod, $reflectionClass)) {
                        throw new AnnotationMisplace(
                            Id::class,
                            $reflectionMethod->getName(),
                            $reflectionClass->name
                        );
                    }
                    if (!$id->getter) {
                        $id->getter = $reflectionMethod->getName();
                    }
                    $this->logger->debug("Found resource ID.");
                    if (!$id->property || !$reflectionClass->hasProperty($id->property)) {
                        $property = lcfirst(preg_replace(self::GETTER, '', $id->getter));
                        $id->property = $reflectionClass->hasProperty($property) ? $property : null;
                    }
                }
                /** @var Attribute $attribute */
                if ($attribute = $this->reader->getMethodAnnotation($reflectionMethod, Attribute::class)) {
                    if (!$attribute->getter && !$this->isGetter($reflectionMethod, $reflectionClass)) {
                        throw new AnnotationMisplace(
                            Attribute::class,
                            $reflectionMethod->getName(),
                            $reflectionClass->name
                        );
                    }
                    if (!$attribute->getter) {
                        $attribute->getter = $reflectionMethod->getName();
                    }
                    if (!$attribute->name) {
                        $attribute->name = lcfirst(preg_replace(self::GETTER, '', $reflectionMethod->getName()));
                    }
                    if ($attribute->setter === null) {
                        if ($reflectionClass->hasMethod(preg_replace(self::GETTER, 'set', $attribute->getter))) {
                            $attribute->setter = preg_replace(self::GETTER, 'set', $attribute->getter);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($attribute->name))) {
                            $attribute->setter = 'set' . ucfirst($attribute->name);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($attribute->property))) {
                            $attribute->setter = 'set' . ucfirst($attribute->property);
                        }
                    }

                    if ($attribute->setter) {
                        try {
                            $setter = new ReflectionMethod($reflectionClass->getName(), $attribute->setter);
                            if ($setter->getNumberOfRequiredParameters() > 1) {
                                throw new DriverException("Setter can have only one required parameter.");
                            }
                            $parameter = $setter->getParameters()[0];
                            $attribute->type = $parameter->getType()->isBuiltin() ? $parameter->getType()
                                : $parameter->getClass()->getName();
                        } catch (ReflectionException $ignored) {
                            //NOSONAR
                        }
                    }
                    $attributes->set($attribute->name, $attribute);
                    $this->logger->debug("Found resource attribute {$attribute->name}.");
                }
                /** @var Relationship $relationship */
                if ($relationship = $this->reader->getMethodAnnotation($reflectionMethod, Relationship::class)) {
                    if (!$relationship->getter && !$this->isGetter($reflectionMethod, $reflectionClass)) {
                        throw new AnnotationMisplace(
                            Relationship::class,
                            $reflectionMethod->getName(),
                            $reflectionClass->name
                        );
                    }
                    if (!$relationship->getter) {
                        $relationship->getter = $reflectionMethod->getName();
                    }
                    if (!$relationship->name) {
                        $relationship->name = lcfirst(preg_replace(self::GETTER, '', $reflectionMethod->getName()));
                    }
                    if (!$relationship->setter) {
                        if ($reflectionClass->hasMethod(preg_replace(self::GETTER, 'set', $relationship->getter))) {
                            $relationship->setter = preg_replace(self::GETTER, 'set', $relationship->getter);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($relationship->name))) {
                            $relationship->setter = 'set' . ucfirst($relationship->name);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($relationship->property))) {
                            $relationship->setter = 'set' . ucfirst($relationship->property);
                        }
                    }
                    try {
                        if (($reflectionMethod->getReturnType()->isBuiltin()
                                && ($reflectionMethod->getReturnType()->getName() === 'array')) ||
                            ((new ReflectionClass($reflectionMethod->getReturnType()->getName()))
                                ->implementsInterface(Traversable::class))
                        ) {
                            $relationship->isCollection = true;
                        }
                    } catch (ReflectionException $ignored) {
                        //NOSONAR
                    }
                    $relationships->set($relationship->name, $relationship);
                    $this->logger->debug("Found resource relationship {$relationship->name}.");
                }
            }
        }
    }

    /**
     * This method try determine, if method on class is getter.
     *
     * @param ReflectionMethod $reflectionMethod
     * @param ReflectionClass  $reflectionClass
     * @return bool
     */
    private function isGetter(ReflectionMethod $reflectionMethod, ReflectionClass $reflectionClass): bool
    {
        return !(
            (!$reflectionMethod->hasReturnType()) ||
            (
                ($reflectionMethod->getReturnType()->isBuiltin() === true) &&
                ($reflectionMethod->getReturnType()->getName() === 'void')
            )
        );
    }
}
