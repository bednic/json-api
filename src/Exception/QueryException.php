<?php


namespace JSONAPI\Exception;


class QueryException extends JsonApiException
{
    const UNKNOWN = 50;
    const PARSE_ERROR = 51;
    const INVALID_URL = 52;

    public function getStatus(): int
    {
        return 400;
    }
}
