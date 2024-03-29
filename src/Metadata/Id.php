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
    public ?string $property = null;
    public ?string $getter = null;

    /**
     * Id constructor.
     *
     * @param string|null $property
     * @param string|null $getter
     */
    protected function __construct(
        ?string $property = null,
        ?string $getter = null
    ) {
        $this->getter = $getter;
        $this->property = $property;
    }

    /**
     * @param string $property
     *
     * @return Id
     */
    public static function createByProperty(string $property): Id
    {
        $self = new self();
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
        $self = new self();
        $self->getter = $getter;
        return $self;
    }
}
