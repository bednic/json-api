<?php

namespace JSONAPI\Exception\Metadata;

class NameUsedAlready extends MetadataException
{
    protected $code = 545;
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
