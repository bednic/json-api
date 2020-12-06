<?php

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class Id
 *
 * @package JSONAPI\Annotation
 */

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
final class Id extends \JSONAPI\Metadata\Id
{
    /**
     * Id constructor.
     *
     * @param string|null $property
     * @param string|null $getter
     */
    public function __construct(string $property = null, string $getter = null)
    {
        $this->property = $property;
        $this->getter   = $getter;
    }
}
