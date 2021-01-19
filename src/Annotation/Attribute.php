<?php

/**
 * Created by tomas.benedikt@gmail.com
 */

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class Attribute
 *
 * @package JSONAPI\Annotation
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
final class Attribute extends \JSONAPI\Metadata\Attribute
{
    /**
     * @inheritDoc
     */
    public function __construct(
        string $name = null,
        string $property = null,
        string $getter = null,
        string $setter = null,
        string $type = null,
        string $of = null
    ) {
        parent::__construct($name, $property, $getter, $setter, $type, $of);
    }
}
