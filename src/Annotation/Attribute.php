<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:17
 */

namespace JSONAPI\Annotation;

/**
 * Class Attribute
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Attribute extends Field
{
    /**
     * @var string
     */
    public ?string $type = null;

    /**
     * In case of type=array, this represents items data type
     * @var string
     */
    public ?string $of = null;
}
