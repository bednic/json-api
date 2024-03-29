<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Metadata;

use JSONAPI\Exception\JsonApiException;

/**
 * Class MetadataException
 *
 * @package JSONAPI\Exception\Metadata
 */
class MetadataException extends JsonApiException
{
    /**
     * @var int
     */
    protected $code = 540;
    /**
     * @var string
     */
    protected $message = "Unknown Metadata Exception";
}
