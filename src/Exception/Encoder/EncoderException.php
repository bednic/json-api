<?php


namespace JSONAPI\Exception\Encoder;


use JSONAPI\Exception\JsonApiException;

/**
 * Class EncoderException
 *
 * @package JSONAPI\Exception\Encoder
 */
class EncoderException extends JsonApiException
{
    protected $code = 30;
    protected $message = "Unknown Encoder exception";
}
