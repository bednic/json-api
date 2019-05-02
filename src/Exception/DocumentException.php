<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:53
 */

namespace JSONAPI\Exception;

/**
 * Class DocumentException
 * @package JSONAPI\Exception
 */
class DocumentException extends JsonApiException
{
    public static function for(int $code,array $args = []): DocumentException
    {
        if (!isset(self::$messages[$code])) {
            $code = self::DOCUMENT_UNKNOWN;
        }
        return parent::for($code, $args);
    }
}
