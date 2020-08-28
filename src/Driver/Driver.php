<?php

declare(strict_types=1);

namespace JSONAPI\Driver;

use Doctrine\Common\Collections\Collection;
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
use ReflectionProperty;
use ReflectionType;

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

        if (
            !(($getter->hasReturnType() && $getter->getReturnType()->getName() !== 'void')
            || preg_match(self::GETTER, $getter->getName()))
        ) {
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
        return $parameter->getType() ? $parameter->getType()->getName() : null;
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
     * @param ReflectionMethod|ReflectionProperty $reflection
     *
     * @return string|null
     */
    protected function getType($reflection): ?string
    {
        /** @var ReflectionType $type */
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

        $type = $reflection instanceof ReflectionMethod ? $reflection->getReturnType() : $reflection->getType();
        if (is_null($type)) {
            throw new BadSignature($reflection->getName(), $reflection->getDeclaringClass()->getName());
        }
        try {
            if ((new ReflectionClass($type->getName()))->implementsInterface(Collection::class)) {
                return true;
            }
            return false;
        } catch (ReflectionException $exception) {
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
     * @param Relationship         $relationship
     * @param                      $reflection
     * @param ReflectionClass      $reflectionClass
     *
     * @throws BadSignature
     * @throws MethodNotExist
     */
    protected function fillUpRelationship(
        Relationship $relationship,
        $reflection,
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
