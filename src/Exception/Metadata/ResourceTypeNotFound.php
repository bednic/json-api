<?php

namespace JSONAPI\Exception\Metadata;

/**
 * Class ResourceTypeNotFound
 *
 * @package JSONAPI\Exception\Metadata
 */
class ResourceTypeNotFound extends MetadataException
{
    protected $code = 545;
    protected $message = "Resource type %s does not exist.";

    /**
     * RelationNotFound constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $message = sprintf($this->message, $type);
        parent::__construct($message);
    }
}
