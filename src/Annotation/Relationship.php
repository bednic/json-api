<?php

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class Relationship
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
final class Relationship extends \JSONAPI\Metadata\Relationship
{

    public function __construct()
    {
        // Override parent constructor cause Doctrine Annotations need public constructor
    }
}
