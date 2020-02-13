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
    public function getStatus()
    {
        return StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE;
    }
}
