<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;

class Conflict extends BadRequest
{
    protected $code = StatusCodeInterface::STATUS_CONFLICT;
    protected $message = 'Conflict';

    public function getStatus()
    {
        return StatusCodeInterface::STATUS_CONFLICT;
    }
}
