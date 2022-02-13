<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Metadata;

/**
 * Class MetadataNotFound
 *
 * @package JSONAPI\Exception\Metadata
 */
class MetadataNotFound extends MetadataException
{
    /**
     * @var int
     */
    protected $code = 546;
    /**
     * @var string
     */
    protected $message = "ClassMetadata for [%s] does not exist.";

    /**
     * RelationNotFound constructor.
     *
     * @param string $key - class name or resource type
     */
    public function __construct(string $key)
    {
        $message = sprintf($this->message, $key);
        parent::__construct($message);
    }
}
