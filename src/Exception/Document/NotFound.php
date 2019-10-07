<?php

namespace JSONAPI\Exception\Document;

use Fig\Http\Message\StatusCodeInterface;

class NotFound extends BadRequest
{
    protected $code = StatusCodeInterface::STATUS_NOT_FOUND;
    protected $message = "Not Found";
}
