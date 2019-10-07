<?php

namespace JSONAPI\Exception\Query;

use JSONAPI\Exception\JsonApiException;

class MethodNotImplemented extends JsonApiException
{
    protected $code = 20;
    protected $message = "Method is not implemented";
}
