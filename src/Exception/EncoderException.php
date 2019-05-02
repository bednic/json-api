<?php


namespace JSONAPI\Exception;



class EncoderException extends JsonApiException
{
    public static function for(int $code, array $args = []): EncoderException
    {
        if (!isset(self::$messages[$code])) {
            $code = self::ENCODER_UNKNOWN;
        }
        return parent::for($code, $args);
    }
}
