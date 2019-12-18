<?php

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;

class MethodNotAllowed extends BadRequest
{
    protected $code = StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED;
    protected $message = "Method Is Not Implemented";

    public function getStatus()
    {
        return StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED;
    }
}
