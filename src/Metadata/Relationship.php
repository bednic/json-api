<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:28
 */

namespace JSONAPI\Metadata;

/**
 * Class Relationship
 *
 * @package JSONAPI\Metadata
 */
class Relationship extends Field
{
    /**
     * @var string
     * @Required
     */
    public string $target;

    /**
     * @var bool
     */
    public ?bool $isCollection = null;

    /**
     * @var \JSONAPI\Metadata\Meta
     */
    public ?Meta $meta = null;

    /**
     * Relationship constructor.
     */
    private function __construct()
    {
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
        bool $isCollection = false,
        Meta $meta = null
    ): Relationship {
        $self = new static();
        $self->property = $property;
        $self->name = $name;
        $self->target = $target;
        $self->isCollection = $isCollection;
        $self->meta = $meta;
        return $self;
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
        bool $isCollection = false,
        Meta $meta = null
    ): Relationship {
        $self = new static();
        $self->getter = $getter;
        $self->name = $name;
        $self->setter = $setter;
        $self->target = $target;
        $self->isCollection = $isCollection;
        $self->meta = $meta;
        return $self;
    }
}
