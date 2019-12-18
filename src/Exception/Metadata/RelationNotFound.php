<?php

namespace JSONAPI\Exception\Metadata;

/**
 * Class RelationNotFound
 *
 * @package JSONAPI\Exception\Metadata
 */
class RelationNotFound extends MetadataException
{
    protected $code = 544;
    protected $message = "Relation name %s does not exist on Resource %s.";

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
