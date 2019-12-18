<?php

namespace JSONAPI\Annotation;

/**
 * Class Meta
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Meta
{
    /**
     * @var string
     * @Required
     */
    public string $getter;
}
