<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:43
 */

namespace JSONAPI\Metadata;

/**
 * Class IdMetadata
 *
 * @package JSONAPI\Metadata
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
final class Id
{
    /**
     * @var string
     */
    public ?string $property = null;
    /**
     * @var string
     */
    public ?string $getter = null;

    /**
     * @param string $property
     *
     * @return Id
     */
    public static function createByProperty(string $property): Id
    {
        $self = new static();
        $self->property = $property;
        return $self;
    }

    /**
     * @param string $getter
     *
     * @return Id
     */
    public static function createByMethod(string $getter): Id
    {
        $self = new static();
        $self->getter = $getter;
        return $self;
    }
}
