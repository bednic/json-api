<?php

namespace JSONAPI\Exception\Metadata;

use JSONAPI\Exception\JsonApiException;

/**
 * Class MetadataException
 *
 * @package JSONAPI\Exception\Metadata
 */
class MetadataException extends JsonApiException
{
    protected $code = 540;
    protected $message = "Unknown Metadata Exception";
}
