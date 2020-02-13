<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;

/**
 * Class Conflict
 *
 * @package JSONAPI\Exception\Http
 */
class Conflict extends BadRequest
{
    protected $code = StatusCodeInterface::STATUS_CONFLICT;
    protected $message = 'Conflict';

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return StatusCodeInterface::STATUS_CONFLICT;
    }
}
