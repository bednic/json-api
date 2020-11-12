<?php

declare(strict_types=1);

namespace JSONAPI\Exception;

use Exception;
use Fig\Http\Message\StatusCodeInterface;

use function Symfony\Component\String\s;

/**
 * Class JsonApiException
 *
 * @package JSONAPI\Exception
 */
abstract class JsonApiException extends Exception
{
    protected $code    = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    protected $message = "Internal Server Error.";

    /**
     * a short, human-readable summary of the problem that SHOULD NOT change from occurrence to occurrence of the
     * problem, except for purposes of localization.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return s(preg_replace('/^(\w+\\\)*/', '', static::class))
            ->snake()
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    /**
     * the HTTP status code applicable to this problem, expressed as a string value.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }
}
