<?php


namespace JSONAPI\Exception;


class FactoryException extends JsonApiException
{
    public static function for(int $code, array $args = []): FactoryException
    {
        if (!isset(self::$messages[$code])) {
            $code = self::FACTORY_UNKNOWN;
        }
        return parent::for($code, $args);
    }
}
