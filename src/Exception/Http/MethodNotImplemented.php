<?php

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;

class MethodNotImplemented extends BadRequest
{
    protected $code = 41;
    protected $message = "Method Is Not Implemented";

    public function getStatus()
    {
        return StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED;
    }
}
