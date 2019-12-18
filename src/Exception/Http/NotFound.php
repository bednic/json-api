<?php

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;

class NotFound extends BadRequest
{
    protected $code = StatusCodeInterface::STATUS_NOT_FOUND;
    protected $message = "Not Found";
    public function getStatus()
    {
        return StatusCodeInterface::STATUS_NOT_FOUND;
    }
}
