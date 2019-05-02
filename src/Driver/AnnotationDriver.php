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
use JSONAPI\ClassMetadata;
use JSONAPI\Exception\DriverException;
use JSONAPI\Exception\JsonApiException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class AnnotationDriver
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
     * AnnotationDriver constructor.
     * @param LoggerInterface|null $logger
     * @throws AnnotationException
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ? $logger : new NullLogger();
        $this->reader = new AnnotationReader();
    }

    /**
     * Returns metadata for provided class or null if class is not Resource
     * @param string $className
     * @return ClassMetadata|null
     * @throws DriverException
     */
    public function getClassMetadata(string $className): ?ClassMetadata
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
                return new ClassMetadata($id, $resource, $attributes, $relationships);
            }
        } catch (ReflectionException $e) {
            // class does not exist
        }
        return null;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param                  $id
     * @param                  $attributes
     * @param                  $relationships
     */
    private function parseProperties(ReflectionClass $reflectionClass, &$id, ArrayCollection &$attributes, ArrayCollection &$relationships): void
    {
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
     * @param ReflectionClass $reflectionClass
     * @param                  $id
     * @param ArrayCollection $attributes
     * @param ArrayCollection $relationships
     * @throws DriverException
     */
    private function parseMethods(ReflectionClass $reflectionClass, &$id, ArrayCollection &$attributes, ArrayCollection &$relationships): void
    {
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (!$reflectionMethod->isConstructor() && !$reflectionMethod->isDestructor()) {
                if (!$id && ($id = $this->reader->getMethodAnnotation($reflectionMethod, Id::class))) {
                    if (!$reflectionMethod->hasReturnType()) {
                        throw DriverException::for(
                            DriverException::DRIVER_ANNOTATION_NOT_ON_GETTER,
                            [Id::class, $reflectionMethod->getName(), $reflectionClass->name]
                        );
                    }
                    if (!$id->getter) {
                        $id->getter = $reflectionMethod->getName();
                    }
                    $this->logger->debug("Found resource ID.");
                    if (!$id->property || !$reflectionClass->hasProperty($id->property)) {
                        $property = lcfirst(str_replace(['get', 'is'], '', $id->getter));
                        $id->property = $reflectionClass->hasProperty($property) ? $property : null;
                    }
                }
                /** @var Attribute $attribute */
                if ($attribute = $this->reader->getMethodAnnotation($reflectionMethod, Attribute::class)) {
                    if (
                        (!$attribute->getter && !$reflectionMethod->hasReturnType())
                        ||
                        (
                            /* void */
                            ($reflectionMethod->getReturnType()->isBuiltin() === true)
                            && ($reflectionMethod->getReturnType()->getName() === 'void')
                        )
                        ||
                        (
                            /* fluent setters */
                            ($reflectionMethod->getReturnType()->isBuiltin() === false)
                            && ($reflectionMethod->getReturnType()->getName() === $reflectionClass->name)
                        )
                    ) {
                        throw DriverException::for(
                            DriverException::DRIVER_ANNOTATION_NOT_ON_GETTER,
                            [Attribute::class, $reflectionMethod->getName(), $reflectionClass->name]
                        );
                    }
                    if (!$attribute->getter) {
                        $attribute->getter = $reflectionMethod->getName();
                    }
                    if (!$attribute->name) {
                        $attribute->name = lcfirst(str_replace(['get', 'is'], '',
                            $reflectionMethod->getName()));
                    }
                    if (!$attribute->property || !$reflectionClass->hasProperty($attribute->property)) {
                        $property = lcfirst(str_replace(['get', 'is'], '', $attribute->getter));
                        $attribute->property = $reflectionClass->hasProperty($property) ? $property : null;
                    }

                    if ($attribute->setter === null) {
                        if ($reflectionClass->hasMethod(str_replace(['get', 'is'], 'set', $attribute->getter))) {
                            $attribute->setter = str_replace(['get', 'is'], 'set', $attribute->getter);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($attribute->name))) {
                            $attribute->setter = 'set' . ucfirst($attribute->name);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($attribute->property))) {
                            $attribute->setter = 'set' . ucfirst($attribute->property);
                        }
                    }
                    $attributes->set($attribute->name, $attribute);
                    $this->logger->debug("Found resource attribute {$attribute->name}.");
                }
                /** @var Relationship $relationship */
                if ($relationship = $this->reader->getMethodAnnotation($reflectionMethod, Relationship::class)) {
                    if (!$reflectionMethod->hasReturnType()) {
                        throw DriverException::for(
                            DriverException::DRIVER_ANNOTATION_NOT_ON_GETTER,
                            [Relationship::class, $reflectionMethod->getName(), $reflectionClass->name]
                        );
                    }
                    if (!$relationship->getter) {
                        $relationship->getter = $reflectionMethod->getName();
                    }
                    if (!$relationship->name) {
                        $relationship->name = lcfirst(str_replace(['get'], '', $reflectionMethod->getName()));
                    }
                    if (!$relationship->property || !$reflectionClass->hasProperty($relationship->property)) {
                        $property = lcfirst(str_replace(['get'], '', $relationship->getter));
                        $relationship->property = $reflectionClass->hasProperty($property) ? $property : null;
                    }
                    if (!$relationship->setter) {
                        if ($reflectionClass->hasMethod(str_replace(['get'], 'set', $relationship->getter))) {
                            $relationship->setter = str_replace(['get'], 'set', $relationship->getter);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($relationship->name))) {
                            $relationship->setter = 'set' . ucfirst($relationship->name);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($relationship->property))) {
                            $relationship->setter = 'set' . ucfirst($relationship->property);
                        }
                    }
                    try {
                        if (
                            ($reflectionMethod->getReturnType()->isBuiltin() && $reflectionMethod->getReturnType()->getName() === 'array') ||
                            ((new ReflectionClass($reflectionMethod->getReturnType()->getName()))->implementsInterface(\Traversable::class))
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
}
