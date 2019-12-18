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
class Relationship extends Field
{
    /**
     * @var string
     * @Required
     */
    public string $target;

    /**
     * @var bool
     */
    public ?bool $isCollection = null;

    /**
     * @var \JSONAPI\Annotation\Meta
     */
    public ?Meta $meta = null;
}
