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
    public ?string $setter = null;
    public ?string $getter = null;
    public ?string $name = null;
    public ?string $property = null;
    public ?bool $nullable = null;

    /**
     * Field constructor.
     *
     * @param string|null $name
     * @param string|null $property
     * @param string|null $getter
     * @param string|null $setter
     * @param bool|null   $nullable
     */
    public function __construct(
        ?string $name = null,
        ?string $property = null,
        ?string $getter = null,
        ?string $setter = null,
        ?bool $nullable = null
    ) {
        $this->name     = $name;
        $this->property = $property;
        $this->getter   = $getter;
        $this->setter   = $setter;
        $this->nullable = $nullable;
    }
}
