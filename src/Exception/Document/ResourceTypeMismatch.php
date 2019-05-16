<?php


namespace JSONAPI\Exception\Document;

/**
 * Class ResourceTypeMismatch
 *
 * @package JSONAPI\Exception\Http
 */
class ResourceTypeMismatch extends BadRequest
{
    protected $message = "Document primary Resource::type is not same as type of primary data";
}
