<?php


namespace JSONAPI\Exception;


class FactoryException extends JsonApiException
{
    const UNKNOWN = 10;
    const CLASS_IS_NOT_RESOURCE = 11;
    const PATH_IS_NOT_VALID = 12;
}
