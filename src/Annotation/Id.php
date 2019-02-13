<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:43
 */

namespace OpenAPI\Annotation;

/**
 * Class Id
 * @package OpenAPI\Annotation
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Id
{
    /**
     * @var string
     */
    public $property;
    /**
     * @var string
     */
    public $getter = null;
}
