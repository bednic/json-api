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
 *
 * @package JSONAPI\Annotation
 */
class Field
{
    /**
     * @var string
     */
    public ?string $name = null;

    /**
     * @var string
     */
    public ?string $property = null;

    /**
     * @var string
     */
    public ?string $getter = null;

    /**
     * @var string
     */
    public ?string $setter = null;
}