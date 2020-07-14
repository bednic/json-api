<?php

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class Meta
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Meta extends \JSONAPI\Metadata\Meta
{
    public function __construct()
    {
        // Override parent constructor cause Doctrine Annotations need public constructor
    }
}
