<?php

declare(strict_types=1);

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
    protected $code = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    protected $message = "Internal Server Error.";

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }
}
