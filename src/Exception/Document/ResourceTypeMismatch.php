<?php

namespace JSONAPI\Exception\Document;

/**
 * Class ResourceTypeMismatch
 *
 * @package JSONAPI\Exception\Document
 */
class ResourceTypeMismatch extends DocumentException
{
    protected $code = 523;
    protected $message = "Provided data resource type is not same as requested resource type.";
}
