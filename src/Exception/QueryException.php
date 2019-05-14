<?php


namespace JSONAPI\Exception;

/**
 * Class QueryException
 *
 * @package JSONAPI\Exception
 */
class QueryException extends JsonApiException
{
    const UNKNOWN = 50;
    const PARSE_ERROR = 51;
    const INVALID_URL = 52;
}
