<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Metadata;

/**
 * Class InvalidField
 *
 * @package JSONAPI\Exception\Metadata
 */
class InvalidField extends MetadataException
{
    /**
     * @var int
     */
    protected $code = 542;
    /**
     * @var string
     */
    protected $message = "Field [%s] is not Attribute nor Relationship";

    /**
     * InvalidField constructor.
     *
     * @param string $fieldName
     */
    public function __construct(string $fieldName)
    {
        $message = sprintf($this->message, $fieldName);
        parent::__construct($message);
    }
}
