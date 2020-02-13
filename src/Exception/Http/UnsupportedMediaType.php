<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Http;

use Fig\Http\Message\StatusCodeInterface;

/**
 * Class UnsupportedMediaTypeException
 *
 * @package JSONAPI\Exception
 */
class UnsupportedMediaType extends BadRequest
{

    protected $code = StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE;
    protected $message = "Unsupported Media Type";

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE;
    }
}
