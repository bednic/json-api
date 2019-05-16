<?php


namespace JSONAPI\Exception\Document;

use Fig\Http\Message\StatusCodeInterface;
use JSONAPI\Exception\JsonApiException;

/**
 * Class BadRequest
 *
 * @package JSONAPI\Exception\Http
 */
class BadRequest extends JsonApiException
{
    protected $code = StatusCodeInterface::STATUS_BAD_REQUEST;
    protected $message = "Bad Request";

    public function getStatus()
    {
        return $this->code;
    }
}
