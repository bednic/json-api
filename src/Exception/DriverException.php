<?php


namespace JSONAPI\Exception;

/**
 * Class DriverException
 *
 * @package JSONAPI\Exception
 */
class DriverException extends JsonApiException
{
    const UNKNOWN = 20;
    const ANNOTATION_NOT_ON_GETTER = 21;
}
