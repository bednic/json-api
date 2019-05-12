<?php


namespace JSONAPI\Exception;

/**
 * Class EncoderException
 *
 * @package JSONAPI\Exception
 */
class EncoderException extends JsonApiException
{
    const UNKNOWN = 40;
    const INVALID_FIELD = 43;
    const CLASS_NOT_EXIST = 44;
}
