<?php

namespace JSONAPI\Exception;

use Exception;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Class JsonApiException
 *
 * @package JSONAPI\Exception
 */
abstract class JsonApiException extends Exception
{
    protected $code = 50;
    protected $message = "Internal Server Error.";
    /**
     * @return int
     */
    public function getStatus()
    {
        return StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }
}
