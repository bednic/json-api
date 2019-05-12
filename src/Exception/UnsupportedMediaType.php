<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.02.2019
 * Time: 19:47
 */

namespace JSONAPI\Exception;

/**
 * Class UnsupportedMediaType
 *
 * @package JSONAPI\Exception
 */
class UnsupportedMediaType extends JsonApiException
{

    protected $message = "Unsupported Media Type";

    public function getStatus(): int
    {
        return 415;
    }
}
