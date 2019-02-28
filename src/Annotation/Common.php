<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 13:30
 */

namespace JSONAPI\Annotation;

/**
 * Class Common
 * @package JSONAPI\Annotation
 */
class Common
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $property;

    /**
     * @var string
     */
    public $getter = null;

    /**
     * @var string
     */
    public $setter = null;
}
