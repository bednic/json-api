<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 11:00
 */

namespace OpenAPI\Driver;


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use OpenAPI\Annotation\Attribute;
use OpenAPI\Annotation\Id;
use OpenAPI\Annotation\Relationship;
use OpenAPI\Annotation\Resource;
use OpenAPI\ClassMetadata;
use OpenAPI\Exception\ClassMetadataException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class AnnotationDriver
 * @package OpenAPI\Driver
 */
class AnnotationDriver implements IDriver
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
     * @param string $className
     * @return ClassMetadata|null
     * @throws ClassMetadataException
     * @throws \ReflectionException
     */
    public function getClassMetadata(string $className): ?ClassMetadata
    {
        $ref = new \ReflectionClass($className);
        /** @var Resource | null $resource */
        if ($resource = $this->reader->getClassAnnotation($ref, Resource::class)) {
            $this->logger->debug("Found resource {$resource->type}.");
            $id = null;
            $attributes = [];
            $relationships = [];
            $this->parseProperties($ref,$id, $attributes, $relationships);
            $this->parseMethods($ref, $id,$attributes, $relationships);
            $this->logger->info("Created ClassMetadata for <{$resource->type}>");
            return new ClassMetadata($id, $resource, $attributes, $relationships);
        }
        return null;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param $id
     * @param $attributes
     * @param $relationships
     */
    private function parseProperties(\ReflectionClass $reflectionClass, &$id, &$attributes, &$relationships): void
    {
        foreach ($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            /** @var Id | null $id */
            if (!$id && ($id = $this->reader->getPropertyAnnotation($reflectionProperty, Id::class))) {
                if (!$id->property) $id->property = $reflectionProperty->getName();
                $this->logger->debug("Found resource ID.");
            }
            /** @var Attribute | null $attribute */
            if ($attribute = $this->reader->getPropertyAnnotation($reflectionProperty, Attribute::class)) {
                if (!$attribute->name) $attribute->name = $reflectionProperty->getName();
                if (!$attribute->property) $attribute->property = $reflectionProperty->getName();
                $attributes[$attribute->name] = $attribute;
                $this->logger->debug("Found resource attribute {$attribute->name}.");
            }
            /** @var Relationship | null $relationship */
            if ($relationship = $this->reader->getPropertyAnnotation($reflectionProperty, Relationship::class)) {
                if (!$relationship->name) $relationship->name = $reflectionProperty->getName();
                if (!$relationship->property) $relationship->property = $reflectionProperty->getName();
                $relationships[$relationship->name] = $relationship;
                $this->logger->debug("Found resource relationship {$relationship->name}.");
            }
        }
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param $id
     * @param $attributes
     * @param $relationships
     * @throws ClassMetadataException
     */
    private function parseMethods(\ReflectionClass $reflectionClass,&$id, &$attributes, &$relationships): void
    {
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (!$reflectionMethod->isConstructor() && !$reflectionMethod->isDestructor()) {
                if (!$id && ($id = $this->reader->getMethodAnnotation($reflectionMethod, Id::class))) {
                    if (!$reflectionMethod->hasReturnType()) throw new ClassMetadataException('Id annotation on method MUST be getter.');
                    if (!$id->getter) $id->getter = $reflectionMethod->getName();
                    $this->logger->debug("Found resource ID.");
                }

                if ($attribute = $this->reader->getMethodAnnotation($reflectionMethod, Attribute::class)) {
                    if (!$reflectionMethod->hasReturnType()) throw new ClassMetadataException('Annotation Attribute on method MUST be on getter.');
                    if (!$attribute->getter) $attribute->getter = $reflectionMethod->getName();
                    if (!$attribute->name) $attribute->name = lcfirst(str_replace('get', '', $reflectionMethod->getName()));
                    if (!$attribute->property || !$reflectionClass->hasProperty($attribute->property)) {
                        $property = lcfirst(str_replace('get', '', $attribute->getter));
                        $attribute->property = $reflectionClass->hasProperty($property) ? $property : null;
                    }

                    if (!$attribute->setter) {
                        if ($reflectionClass->hasMethod(str_replace('get', 'set', $attribute->getter))) {
                            $attribute->setter = str_replace('get', 'set', $attribute->getter);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($attribute->name))) {
                            $attribute->setter = 'set' . ucfirst($attribute->name);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($attribute->property))) {
                            $attribute->setter = 'set' . ucfirst($attribute->property);
                        } else {
                            throw new ClassMetadataException("Setter for attribute {$attribute->name} is not defined");
                        }
                    }
                    $attributes[$attribute->name] = $attribute;
                    $this->logger->debug("Found resource attribute {$attribute->name}.");
                }
                if ($relationship = $this->reader->getMethodAnnotation($reflectionMethod, Relationship::class)) {
                    if (!$reflectionMethod->hasReturnType()) throw new ClassMetadataException('Annotation Relationship on method MUST be on getter.');
                    if (!$relationship->getter) $relationship->getter = $reflectionMethod->getName();
                    if (!$relationship->name) $relationship->name = lcfirst(str_replace('get', '', $reflectionMethod->getName()));

                    if (!$relationship->property || !$reflectionClass->hasProperty($relationship->property)) {
                        $property = lcfirst(str_replace('get', '', $relationship->getter));
                        $relationship->property = $reflectionClass->hasProperty($property) ? $property : null;
                    }

                    if (!$relationship->setter) {
                        if ($reflectionClass->hasMethod(str_replace('get', 'set', $relationship->getter))) {
                            $relationship->setter = str_replace('get', 'set', $relationship->getter);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($relationship->name))) {
                            $relationship->setter = 'set' . ucfirst($relationship->name);
                        } elseif ($reflectionClass->hasMethod('set' . ucfirst($relationship->property))) {
                            $relationship->setter = 'set' . ucfirst($relationship->property);
                        } else {
                            throw new ClassMetadataException("Setter for relationship {$relationship->name} is not defined");
                        }
                    }
                    $relationships[$relationship->name] = $relationship;
                    $this->logger->debug("Found resource relationship {$relationship->name}.");
                }
            }
        }
    }
}
