<?php

namespace JSONAPI\Exception\Driver;

use JSONAPI\Exception\JsonApiException;

/**
 * Class DriverException
 *
 * @package JSONAPI\Exception
 */
class DriverException extends JsonApiException
{
    protected $code = 10;
    protected $message = "Unknown Driver exception";
}
