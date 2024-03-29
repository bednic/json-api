<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Exception\JsonApiException;

/**
 * Class BadRequest
 *
 * @package JSONAPI\Exception\Http
 */
class BadRequest extends JsonApiException
{
    /**
     * @var int
     */
    protected $code = StatusCodeInterface::STATUS_BAD_REQUEST;
    /**
     * @var string
     */
    protected $message = "Bad Request";

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return StatusCodeInterface::STATUS_BAD_REQUEST;
    }
}
