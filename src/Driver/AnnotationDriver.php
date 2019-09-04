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
use JSONAPI\Annotation\Common;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\ClassNotExist;
use JSONAPI\Exception\Driver\ClassNotResource;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Metadata\ClassMetadata;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class AnnotationDriver
 *
 * @package JSONAPI\Driver
 */
class AnnotationDriver
{
    /**
     * Regex patter for getters
     */
    private const GETTER = '/^(get|is|has)/';
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
     *
     * @param LoggerInterface|null $logger
     *
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
     *
     * @return ClassMetadata
     * @throws AnnotationMisplace
     * @throws ClassNotExist
     * @throws ClassNotResource
     * @throws DriverException
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
     *
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
                    if (!$this->isGetter($reflectionMethod)) {
                        throw new AnnotationMisplace(
                            Id::class,
                            $reflectionMethod->getName(),
                            $reflectionClass->name
                        );
                    }
                    $id->getter = $reflectionMethod->getName();
                    $this->logger->debug("Found resource ID.");
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

                    if ($attribute->setter && $attribute->type === null) {
                        $attribute->type = $this->getSetterParameterType($reflectionClass, $attribute);
                    }
                    $attributes->set($attribute->name, $attribute);
                    $this->logger->debug("Found resource attribute {$attribute->name}.");
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
                    $relationship->getter = $reflectionMethod->getName();
                    if (!$relationship->name) {
                        $relationship->name = $this->getName($reflectionMethod);
                    }
                    if ($relationship->setter === null) {
                        $relationship->setter = $this->getSetter($reflectionClass, $relationship);
                    }
                    $relationship->isCollection = $this->isCollection($reflectionMethod);
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
     *
     * @return bool
     */
    private function isGetter(ReflectionMethod $reflectionMethod): bool
    {
        return $reflectionMethod->hasReturnType()
            && $reflectionMethod->getReturnType()->getName() !== 'void'
            && preg_match(self::GETTER, $reflectionMethod->getName());
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
     * @param Common          $annotation
     *
     * @return string|null
     */
    private function getSetter(ReflectionClass $reflectionClass, Common $annotation): ?string
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
     * @return mixed
     * @throws DriverException
     */
    private function getSetterParameterType(ReflectionClass $reflectionClass, Attribute $attribute)
    {
        try {
            $method = new ReflectionMethod($reflectionClass->getName(), $attribute->setter);
        } catch (Exception $e) {
            throw new DriverException($e->getMessage(), $e->getCode(), $e);
        }
        if ($method->getNumberOfRequiredParameters() > 1) {
            throw new DriverException("Setter can have only one required parameter.");
        }
        $parameters = $method->getParameters();
        $parameter = array_shift($parameters);
        return $parameter->getType()->isBuiltin() ? $parameter->getType() : $parameter->getClass()->getName();
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     * @throws DriverException
     */
    private function isCollection(ReflectionMethod $reflectionMethod): bool
    {
        if ($reflectionMethod->getReturnType()->isBuiltin()
            && $reflectionMethod->getReturnType()->getName() === 'array') {
            throw new DriverException("Collection relationships cannot return array, but "
                . Collection::class . ".");
        }

        try {
            return (new ReflectionClass($reflectionMethod->getReturnType()->getName()))
                ->implementsInterface(Collection::class);
        } catch (Exception $exception) {
            throw new DriverException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
