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
    /**
     * @var int
     */
    protected $code = StatusCodeInterface::STATUS_CONFLICT;
    /**
     * @var string
     */
    protected $message = 'Conflict';

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return StatusCodeInterface::STATUS_CONFLICT;
    }
}
