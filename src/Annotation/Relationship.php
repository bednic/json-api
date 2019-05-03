<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:28
 */

namespace JSONAPI\Annotation;

/**
 * Class Relationship
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"METHOD", "PROPERTY"})
 */
class Relationship extends Common
{
    /**
     * @var string
     * @Required
     */
    public $target;

    /**
     * @var bool
     */
    public $isCollection = false;
}
