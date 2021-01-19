<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

/**
 * Class MetaMetadata
 *
 * @package JSONAPI\Metadata
 */
class Meta
{
    /**
     * Meta constructor.
     *
     * @param string $getter
     */
    protected function __construct(
    public string $getter
    ) {
    }

    /**
     * @param string $getter
     *
     * @return Meta
     */
    public static function create(string $getter): Meta
    {
        return new self($getter);
    }
}
