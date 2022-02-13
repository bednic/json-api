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
    /**
     * @var int
     */
    protected $code = 530;
    /**
     * @var string
     */
    protected $message = "Unknown Driver Exception";
}
