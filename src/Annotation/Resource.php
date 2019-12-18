<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:41
 */

namespace JSONAPI\Annotation;

/**
 * Class Resource
 *
 * @package JSONAPI\Annotation
 * @Annotation
 * @Target({"CLASS"})
 */
class Resource
{
    /**
     * @var string
     * @Required
     */
    public string $type;

    /**
     * @var bool
     */
    public bool $readOnly = false;

    /**
     * @var \JSONAPI\Annotation\Meta
     */
    public ?Meta $meta = null;
}
