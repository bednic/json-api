<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;

/**
 * Class NotFound
 * Throw this exception when you did not found resource
 *
 * @package JSONAPI\Exception\Http
 */
class NotFound extends BadRequest
{
    protected $code = StatusCodeInterface::STATUS_NOT_FOUND;
    protected $message = 'Resource %s with ID %s not found.';

    public function __construct(string $resource, string $id)
    {
        parent::__construct(sprintf($this->message, $resource, $id));
    }

    public function getStatus()
    {
        return StatusCodeInterface::STATUS_NOT_FOUND;
    }
}
