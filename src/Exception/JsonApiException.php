<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 16.04.2019
 * Time: 13:49
 */

namespace JSONAPI\Exception;

/**
 * Class JsonApiException
 *
 * @package JSONAPI\Exception
 */
abstract class JsonApiException extends \Exception
{
    /**
     * @return int
     */
    public function getStatus(): int
    {
        return 500;
    }
}
