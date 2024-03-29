<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Metadata;

/**
 * Class NameUsedAlready
 *
 * @package JSONAPI\Exception\Metadata
 */
class AlreadyInUse extends MetadataException
{
    /**
     * @var int
     */
    protected $code = 545;
    /**
     * @var string
     */
    protected $message = "Field name %s is reserved or used yet. Please use different field name.";

    /**
     * ForbiddenDataType constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $message = sprintf($this->message, $name);
        parent::__construct($message);
    }
}
