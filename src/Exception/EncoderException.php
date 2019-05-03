<?php


namespace JSONAPI\Exception;


class EncoderException extends JsonApiException
{
    const ENCODER_UNKNOWN = 40;
    const ENCODER_INVALID_FIELD = 43;
    const ENCODER_CLASS_NOT_EXIST = 44;
}
