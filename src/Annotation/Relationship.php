<?php


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
    }
}
