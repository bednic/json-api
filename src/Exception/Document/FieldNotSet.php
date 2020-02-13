<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

/**
 * Class FieldNotSet
 *
 * @package JSONAPI\Exception\Document
 */
class FieldNotSet extends DocumentException
{
    protected $code = 526;
    protected $message = "Field %s is not set.";

    /**
     * FieldNotSet constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $message = sprintf($this->message, $field);
        parent::__construct($message);
    }
}
