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
    public string $getter;

    /**
     * Meta constructor.
     *
     * @param string $getter
     */
    protected function __construct(
        string $getter
    ) {
        $this->getter = $getter;
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
