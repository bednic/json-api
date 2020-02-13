<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

use JSONAPI\Exception\JsonApiException;

/**
 * Class DocumentException
 *
 * @package JSONAPI\Exception\Document
 */
class DocumentException extends JsonApiException
{
    protected $code = 520;
    protected $message = "Unknown Document Exception.";
}
