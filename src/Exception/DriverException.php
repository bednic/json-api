<?php


namespace JSONAPI\Exception;


class DriverException extends JsonApiException
{
    const DRIVER_UNKNOWN = 20;
    const DRIVER_ANNOTATION_NOT_ON_GETTER = 21;

}
