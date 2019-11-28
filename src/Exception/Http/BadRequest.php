<?php

namespace JSONAPI\Exception\Http;

use Exception;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Class BadRequest
 *
 * @package JSONAPI\Exception\Http
 */
class BadRequest extends Exception
{
    protected $code = 40;
    protected $message = "Bad Request";

    public function getStatus()
    {
        return StatusCodeInterface::STATUS_BAD_REQUEST;
    }
}
