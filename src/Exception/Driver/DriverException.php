<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

use JSONAPI\Exception\JsonApiException;

/**
 * Class DriverException
 *
 * @package JSONAPI\Exception
 */
class DriverException extends JsonApiException
{
    protected $code = 530;
    protected $message = "Unknown DriverInterface Exception";
}
