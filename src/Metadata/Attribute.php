<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:17
 */

namespace JSONAPI\Metadata;

/**
 * Class Attribute
 *
 * @package JSONAPI\Metadata
 */
class Attribute extends Field
{
    /**
     * @var string
     */
    public ?string $type = null;

    /**
     * In case of type=array, this represents items data type
     *
     * @var string
     */
    public ?string $of = null;

    private function __construct()
    {
    }

    /**
     * @param string      $property
     * @param string|null $name
     * @param string|null $type
     * @param string|null $of
     *
     * @return static
     */
    public static function createByProperty(
        string $property,
        string $of = null,
        string $name = null,
        string $type = null
    ): self {
        $self = new static();
        $self->property = $property;
        $self->name = $name;
        $self->type = $type;
        $self->of = $of;
        return $self;
    }

    /**
     * @param string      $getter
     * @param string|null $setter
     * @param string|null $name
     * @param string|null $type
     * @param string|null $of
     *
     * @return static
     */
    public static function createByMethod(
        string $getter,
        string $setter = null,
        string $name = null,
        string $type = null,
        string $of = null
    ): self {
        $self = new static();
        $self->getter = $getter;
        $self->setter = $setter;
        $self->name = $name;
        $self->type = $type;
        $self->of = $of;
        return $self;
    }
}
