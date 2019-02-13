<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 01.02.2019
 * Time: 0:41
 */

namespace OpenAPI\Annotation;

/**
 * Class Resource
 * @package OpenAPI\Annotation
 * @Annotation
 * @Target({"CLASS"})
 */
class Resource
{
    /**
     * @var string
     * @Required
     */
    public $type;

    /**
     * @var bool
     */
    public $public = true;
}
