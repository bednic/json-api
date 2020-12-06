<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

/**
 * Class Relationship
 *
 * @package JSONAPI\Metadata
 */
class Relationship extends Field
{
    /**
     * Relationship constructor.
     *
     * @param string|null $target
     * @param string|null $name
     * @param string|null $property
     * @param string|null $getter
     * @param string|null $setter
     * @param bool|null   $isCollection
     * @param Meta|null   $meta
     */
    protected function __construct(
        public ?string $target,
        string $name = null,
        string $property = null,
        string $getter = null,
        string $setter = null,
        public ?bool $isCollection = null,
        public ?Meta $meta = null
    ) {
        parent::__construct($name, $property, $getter, $setter);
    }


    /**
     * @param string      $property
     * @param string      $target
     * @param string|null $name
     * @param bool        $isCollection
     * @param Meta|null   $meta
     *
     * @return Relationship
     */
    public static function createByProperty(
        string $property,
        string $target,
        string $name = null,
        bool $isCollection = null,
        Meta $meta = null
    ): Relationship {
        return new self($target, $name, $property, null, null, $isCollection, $meta);
    }

    /**
     * @param string      $getter
     * @param string|null $setter
     * @param string      $target
     * @param string|null $name
     * @param bool        $isCollection
     * @param Meta|null   $meta
     *
     * @return Relationship
     */
    public static function createByMethod(
        string $getter,
        string $target,
        string $setter = null,
        string $name = null,
        bool $isCollection = null,
        Meta $meta = null
    ): Relationship {
        return new self($target, $name, null, $getter, $setter, $isCollection, $meta);
    }
}
