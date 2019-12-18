<?php

namespace JSONAPI\Exception\Metadata;

/**
 * Class MetaNotFound
 *
 * @package JSONAPI\Exception\Metadata
 */
class MetaNotFound extends MetadataException
{
    protected $code = 543;
    protected $message = "Meta name %s does not exist on Resource %s.";

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
