<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

/**
 * Class FieldNotSet
 *
 * @package JSONAPI\Exception\Document
 */
abstract class FieldNotExist extends DocumentException
{
    protected $code = 526;
    protected $message = "Field %s is not set.";
    protected string $field;

    /**
     * FieldNotSet constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $message = sprintf($this->message, $field);
        parent::__construct($message);
        $this->field = $field;
    }
}
