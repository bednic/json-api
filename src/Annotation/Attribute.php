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
class Attribute extends Common
{
    /**
     * @var string
     */
    public $type = null;

    /**
     * @var string
     */
    public $of = null;

    /**
     * Returns if Attribute is on property
     *
     * @return bool
     */
    public function isProperty(): bool
    {
        return !!$this->property;
    }

    /**
     * Return true if attribute has getter but not setter, thus is read-only
     *
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return ($this->getter && !$this->setter);
    }
}
