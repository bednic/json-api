<?php

declare(strict_types=1);

namespace JSONAPI\Exception;

/**
 * Class InvalidArgumentException
 *
 * @package JSONAPI\Exception
 */
class InvalidArgumentException extends JsonApiException
{
    protected $code = 550;
    protected $message = "Unknown Invalid Argument Exception.";
}
