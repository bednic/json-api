<?php

namespace JSONAPI\Metadata;

/**
 * Class MetaMetadata
 *
 * @package JSONAPI\Metadata
 */
class Meta
{
    /**
     * @var string
     * @Required
     */
    public string $getter;

    /**
     * @param string $getter
     *
     * @return Meta
     */
    public static function create(string $getter): Meta
    {
        $self = new static();
        $self->getter = $getter;
        return $self;
    }
}
