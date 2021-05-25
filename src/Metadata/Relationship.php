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
    public ?bool $isCollection = null;
    public ?Meta $meta = null;
    public ?string $target;

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
     * @param bool|null   $nullable
     */
    protected function __construct(
        ?string $target,
        string $name = null,
        string $property = null,
        string $getter = null,
        string $setter = null,
        ?bool $isCollection = null,
        ?Meta $meta = null,
        ?bool $nullable = null
    ) {
        parent::__construct($name, $property, $getter, $setter, $nullable);
        $this->target       = $target;
        $this->meta         = $meta;
        $this->isCollection = $isCollection;
    }


    /**
     * @param string      $property
     * @param string      $target
     * @param string|null $name
     * @param bool        $isCollection
     * @param Meta|null   $meta
     * @param bool|null   $nullable
     *
     * @return Relationship
     */
    public static function createByProperty(
        string $property,
        string $target,
        string $name = null,
        bool $isCollection = null,
        Meta $meta = null,
        bool $nullable = null
    ): Relationship {
        return new self($target, $name, $property, null, null, $isCollection, $meta, $nullable);
    }

    /**
     * @param string      $getter
     * @param string      $target
     * @param string|null $setter
     * @param string|null $name
     * @param bool        $isCollection
     * @param Meta|null   $meta
     * @param bool|null   $nullable
     *
     * @return Relationship
     */
    public static function createByMethod(
        string $getter,
        string $target,
        string $setter = null,
        string $name = null,
        bool $isCollection = null,
        Meta $meta = null,
        bool $nullable = null
    ): Relationship {
        return new self($target, $name, null, $getter, $setter, $isCollection, $meta, $nullable);
    }
}
