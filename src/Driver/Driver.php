<?php

declare(strict_types=1);

namespace JSONAPI\Driver;

use JSONAPI\Data\Collection;
use JSONAPI\Exception\Driver\AnnotationMisplace;
use JSONAPI\Exception\Driver\BadSignature;
use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Exception\Driver\MethodNotExist;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\ClassMetadata;
use JSONAPI\Metadata\Field;
use JSONAPI\Metadata\Relationship;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

/**
 * Interface Driver
 *
 * @package JSONAPI\Metadata
 */
abstract class Driver
{
    /**
     * Regex patter for getters
     */
    private const GETTER = '/^(get|is|has)/';

    /**
     * @param string $className
     *
     * @return ClassMetadata
     * @throws DriverException
     * @throws MetadataException
     */
    abstract public function getClassMetadata(string $className): ClassMetadata;

    /**
     * This method check if annotated method is getter.
     *
     * @param ReflectionMethod $getter
     *
     * @throws AnnotationMisplace
     */
    protected function isGetter(ReflectionMethod $getter): void
    {
        $type = $this->getType($getter);
        if (!(($type && $type !== 'void') || preg_match(self::GETTER, $getter->getName()))) {
            throw new AnnotationMisplace(
                $getter->getName(),
                $getter->class
            );
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param Field           $metadata
     *
     * @return string|null
     */
    protected function getSetter(ReflectionClass $reflectionClass, Field $metadata): ?string
    {
        $setter = preg_replace(self::GETTER, 'set', $metadata->getter);
        if ($reflectionClass->hasMethod($setter)) {
            return $setter;
        }
        return null;
    }

    /**
     * @param ReflectionMethod $setter
     *
     * @return string
     * @throws BadSignature
     */
    protected function getSetterParameterType(ReflectionMethod $setter): ?string
    {
        if ($setter->getNumberOfRequiredParameters() > 1) {
            throw new BadSignature($setter->getName(), $setter->class);
        }
        $parameters = $setter->getParameters();

        $parameter = array_shift($parameters);
        return $this->getType($parameter);
    }

    /**
     * @param ReflectionMethod|ReflectionProperty $reflection
     *
     * @return string
     */
    protected function getName($reflection): string
    {
        if ($reflection instanceof ReflectionProperty) {
            return $reflection->getName();
        } else {
            return lcfirst(preg_replace(self::GETTER, '', $reflection->getName()));
        }
    }

    /**
     * @param ReflectionMethod|ReflectionProperty|ReflectionParameter $reflection
     *
     * @return string|null
     */
    protected function getType($reflection): ?string
    {
        /** @var ReflectionNamedType $type */
        $type = $reflection instanceof ReflectionMethod ? $reflection->getReturnType() : $reflection->getType();
        return $type ? $type->getName() : null;
    }

    /**
     * @param ReflectionProperty|ReflectionMethod $reflection
     *
     * @return bool|null
     * @throws BadSignature
     */
    protected function isCollection($reflection): ?bool
    {
        $type = $this->getType($reflection);
        if (is_null($type)) {
            throw new BadSignature($reflection->getName(), $reflection->getDeclaringClass()->getName());
        } elseif ($type === 'array') {
            return true;
        } elseif ($type === Collection::class) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param ReflectionMethod|ReflectionProperty $reflection
     *
     * @return string|null
     */
    protected function tryGetArrayType($reflection): ?string
    {
        if (
            preg_match(
                '~@return ((null|array)\|)*?((?P<type>\w+)\[\])(\|(null|array))*?~',
                $reflection->getDocComment(),
                $match
            )
        ) {
            return $match['type'];
        }
        return null;
    }

    /**
     * @param Attribute                           $attribute
     * @param ReflectionProperty|ReflectionMethod $reflection
     * @param ReflectionClass                     $reflectionClass
     *
     * @throws BadSignature
     */
    protected function fillUpAttribute(Attribute $attribute, $reflection, ReflectionClass $reflectionClass)
    {
        if (!$attribute->name) {
            $attribute->name = $this->getName($reflection);
        }
        if ($attribute->type === null) {
            $attribute->type = $this->getType($reflection);
        }
        if ($attribute->getter) {
            if ($attribute->setter === null) {
                $attribute->setter = $this->getSetter($reflectionClass, $attribute);
            }
            if ($attribute->type === null && $attribute->setter) {
                try {
                    $attribute->type = $this->getSetterParameterType($reflectionClass->getMethod($attribute->setter));
                } catch (ReflectionException $ignored) {
                    // Can't happen
                }
            }
        }
        if ($attribute->type === 'array' && $attribute->of === null) {
            $attribute->of = $this->tryGetArrayType($reflection);
        }
    }

    /**
     * @param Relationship                                  $relationship
     * @param ReflectionProperty|ReflectionMethod|Reflector $reflection
     * @param ReflectionClass                               $reflectionClass
     *
     * @throws BadSignature
     * @throws MethodNotExist
     */
    protected function fillUpRelationship(
        Relationship $relationship,
        Reflector $reflection,
        ReflectionClass $reflectionClass
    ) {
        if (!$relationship->name) {
            $relationship->name = $this->getName($reflection);
        }
        if ($relationship->isCollection === null) {
            $relationship->isCollection = $this->isCollection($reflection);
        }
        if ($relationship->meta && !$reflectionClass->hasMethod($relationship->meta->getter)) {
            throw new MethodNotExist($relationship->meta->getter, $reflectionClass->getName());
        }
        if ($relationship->getter && is_null($relationship->setter)) {
            $relationship->setter = $this->getSetter($reflectionClass, $relationship);
        }
    }
}
