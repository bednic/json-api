<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:43
 */

namespace JSONAPI\Annotation;

/**
 * Class Id
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Id
{
    /**
     * @var string
     */
    public ?string $property = null;
    /**
     * @var string
     */
    public ?string $getter = null;
}
