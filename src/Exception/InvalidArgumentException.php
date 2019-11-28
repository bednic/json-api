<?php

namespace JSONAPI\Exception;

class InvalidArgumentException extends JsonApiException
{
    protected $code = 51;
    protected $message = "Unknown Invalid Argument Exception.";

}
