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
     * @var string
     */
    public ?string $name = null;

    /**
     * @var string
     */
    public ?string $property = null;

    /**
     * @var string
     */
    public ?string $getter = null;

    /**
     * @var string
     */
    public ?string $setter = null;
}
