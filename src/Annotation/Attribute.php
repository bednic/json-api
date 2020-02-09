<?php

namespace JSONAPI\Annotation;

/**
 * Class Attribute
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
final class Attribute extends \JSONAPI\Metadata\Attribute
{

    public function __construct()
    {
    }
}
