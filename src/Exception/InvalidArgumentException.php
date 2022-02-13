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
    /**
     * @var int
     */
    protected $code = 550;
    /**
     * @var string
     */
    protected $message = "Unknown Invalid Argument Exception.";
}
