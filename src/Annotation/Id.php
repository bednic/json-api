<?php

declare(strict_types=1);

namespace JSONAPI\Annotation;

/**
 * Class Id
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
final class Id extends \JSONAPI\Metadata\Id
{

    public function __construct()
    {
    }
}
