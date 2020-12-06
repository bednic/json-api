<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

/**
 * Class Common
 *
 * @package JSONAPI\Metadata
 */
abstract class Field
{
    /**
     * Field constructor.
     *
     * @param string|null $name
     * @param string|null $property
     * @param string|null $getter
     * @param string|null $setter
     */
    public function __construct(
        public ?string $name = null,
        public ?string $property = null,
        public ?string $getter = null,
        public ?string $setter = null
    ) {
    }
}
