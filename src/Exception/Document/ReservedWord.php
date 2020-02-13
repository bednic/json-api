<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

/**
 * Class ReservedWord
 *
 * @package JSONAPI\Exception\Document
 */
class ReservedWord extends DocumentException
{
    protected $code = 524;
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
