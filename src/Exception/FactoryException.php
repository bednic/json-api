<?php


namespace JSONAPI\Exception;


class FactoryException extends JsonApiException
{
    const FACTORY_UNKNOWN = 10;
    const FACTORY_CLASS_IS_NOT_RESOURCE = 11;
    const FACTORY_PATH_IS_NOT_VALID = 12;
}
