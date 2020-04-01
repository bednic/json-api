<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

/**
 * Class IdMetadata
 *
 * @package JSONAPI\Metadata
 */
class Id
{
    public static string $name = 'id';
    /**
     * @var string
     */
    public ?string $property = null;
    /**
     * @var string
     */
    public ?string $getter = null;

    /**
     * Id constructor.
     */
    private function __construct()
    {
    }

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
