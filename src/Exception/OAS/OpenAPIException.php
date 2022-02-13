<?php

declare(strict_types=1);

namespace JSONAPI\Exception\OAS;

use Exception;
use Throwable;

/**
 * Class OpenAPIException
 *
 * @package JSONAPI\Exception\OAS
 */
class OpenAPIException extends Exception
{
    /**
     * @var string
     */
    protected $message = "OAS Unknown Internal Error";
    /**
     * @var int
     */
    protected $code    = 560;

    public static function createFromPrevious(Throwable $previous): self
    {
        return new self("OAS Unknown Internal Error", 560, $previous);
    }
}
