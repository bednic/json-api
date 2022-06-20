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
use JSONAPI\Schema\Resource;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

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
     * @phpstan-param class-string $className
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
        if (!(($type && $type->getName() !== 'void') || preg_match(self::GETTER, $getter->getName()))) {
            throw new AnnotationMisplace(
                $getter->getName(),
                $getter->class
            );
        }
    }

    /**
     * @param ReflectionMethod|ReflectionProperty|ReflectionParameter $reflection
     *
     * @return ReflectionNamedType|null
     */
    protected function getType(
        ReflectionMethod|ReflectionProperty|ReflectionParameter $reflection
    ): ?ReflectionNamedType {
        /** @phpstan-ignore-next-line */
        return $reflection instanceof ReflectionMethod ? $reflection->getReturnType() : $reflection->getType();
    }

    /**
     * @param Attribute                           $attribute
     * @param ReflectionProperty|ReflectionMethod $reflection
     * @param ReflectionClass<Resource|object>                     $reflectionClass
     *
     * @throws BadSignature
     */
    protected function fillUpAttribute(
        Attribute $attribute,
        ReflectionProperty|ReflectionMethod $reflection,
        ReflectionClass $reflectionClass
    ): void {
        if (!$attribute->name) {
            $attribute->name = $this->getName($reflection);
        }
        if ($attribute->type === null) {
            $attribute->type = $this->getType($reflection)?->getName();
        }
        if ($attribute->nullable === null) {
            $attribute->nullable = $this->getType($reflection)?->allowsNull();
        }
        if ($attribute->getter) {
            if ($attribute->setter === null) {
                $attribute->setter = $this->getSetter($reflectionClass, $attribute);
            }
            if ($attribute->type === null && $attribute->setter) {
                try {
                    $attribute->type = $this->getSetterParameterType($reflectionClass->getMethod($attribute->setter));
                } catch (ReflectionException) {
                    // Can't happen
                }
            }
        }
        if ($attribute->type === 'array' && $attribute->of === null) {
            $attribute->of = $this->tryGetArrayType($reflection);
        }
    }

    /**
     * @param ReflectionMethod|ReflectionProperty $reflection
     *
     * @return string
     */
    protected function getName(ReflectionMethod|ReflectionProperty $reflection): string
    {
        if ($reflection instanceof ReflectionProperty) {
            return $reflection->getName();
        } else {
            return lcfirst(preg_replace(self::GETTER, '', $reflection->getName()));
        }
    }

    /**
     * @param ReflectionClass<Resource|object> $reflectionClass
     * @param Field                            $metadata
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
     * @return string|null
     * @throws BadSignature
     */
    protected function getSetterParameterType(ReflectionMethod $setter): ?string
    {
        if ($setter->getNumberOfRequiredParameters() > 1) {
            throw new BadSignature($setter->getName(), $setter->class);
        }
        $parameters = $setter->getParameters();

        $parameter = array_shift($parameters);
        return $this->getType($parameter)?->getName();
    }

    /**
     * @param ReflectionMethod|ReflectionProperty $reflection
     *
     * @return string|null
     */
    protected function tryGetArrayType(ReflectionProperty|ReflectionMethod $reflection): ?string
    {
        if (
            $reflection->getDocComment() !== false &&
            preg_match(
                '~(@return|@var) ((null|array)\|)*?((?P<type>\w+)\[])(\|(null|array))*?~',
                $reflection->getDocComment(),
                $match
            )
        ) {
            return $match['type'];
        }
        return null;
    }

    /**
     * @param Relationship                        $relationship
     * @param ReflectionProperty|ReflectionMethod $reflection
     * @param ReflectionClass<Resource|object>    $reflectionClass
     *
     * @throws BadSignature
     * @throws MethodNotExist
     */
    protected function fillUpRelationship(
        Relationship $relationship,
        ReflectionProperty|ReflectionMethod $reflection,
        ReflectionClass $reflectionClass
    ): void {
        if (!$relationship->name) {
            $relationship->name = $this->getName($reflection);
        }
        if ($relationship->nullable === null) {
            $relationship->nullable = $this->getType($reflection)?->allowsNull();
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

    /**
     * @param ReflectionProperty|ReflectionMethod $reflection
     *
     * @return bool
     * @throws BadSignature
     */
    protected function isCollection(ReflectionMethod|ReflectionProperty $reflection): bool
    {
        $type = $this->getType($reflection);
        if (is_null($type)) {
            throw new BadSignature($reflection->getName(), $reflection->getDeclaringClass()->getName());
        } elseif ($type->isBuiltin()) {
            $ret = 'array' === $type->getName();
        } elseif ($type->getName() === Collection::class) {
            $ret = true;
        } else {
            try {
                $ret = (new ReflectionClass($type->getName()))->isIterable();
            } catch (ReflectionException) {
                $ret = false;
            }
        }
        return $ret;
    }
}
