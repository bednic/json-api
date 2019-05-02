<?php


namespace JSONAPI\Exception;


class DriverException extends JsonApiException
{
    public static function for(int $code, array $args = []): DriverException
    {
        if (!isset(self::$messages[$code])) {
            $code = self::DRIVER_UNKNOWN;
        }
        return parent::for($code, $args);
    }
}
