<?php

declare(strict_types=1);

namespace JSONAPI\Exception;

class InvalidArgumentException extends JsonApiException
{
    protected $code = 550;
    protected $message = "Unknown Invalid Argument Exception.";
}
