<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Metadata;

/**
 * Class AttributeNotFound
 *
 * @package JSONAPI\Exception\Metadata
 */
class AttributeNotFound extends MetadataException
{
    /**
     * @var int
     */
    protected $code = 541;
    /**
     * @var string
     */
    protected $message = "Attribute metadata name [%s] does not exist on resource [%s].";

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
