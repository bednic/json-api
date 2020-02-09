<?php

namespace JSONAPI\Exception\Metadata;

class AttributeNotFound extends MetadataException
{
    protected $code = 541;
    protected $message = "Attribute metadata name '%s' does not exist on resource '%s'.";

    /**
     * RelationNotFound constructor.
     *
     * @param string $name
     * @param string $resource
     */
    public function __construct(string $name, string $resource)
    {
        $message = sprintf($this->message, $name, $resource);
        parent::__construct($message);
    }
}
