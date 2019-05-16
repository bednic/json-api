<?php


namespace JSONAPI\Exception;


use Exception;

/**
 * Class JsonApiException
 *
 * @package JSONAPI\Exception
 */
abstract class JsonApiException extends Exception
{
    /**
     * @return int
     */
    public function getStatus()
    {
        return 500;
    }
}
