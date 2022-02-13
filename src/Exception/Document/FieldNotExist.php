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
    /**
     * @var int
     */
    protected $code = 526;
    /**
     * @var string
     */
    protected $message = "Field %s is not set.";
    /**
     * @var string
     */
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
