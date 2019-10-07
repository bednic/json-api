<?php

namespace JSONAPI\Exception\Encoder;

/**
 * Class InvalidField
 *
 * @package JSONAPI\Exception\Encoder
 */
class InvalidField extends EncoderException
{
    protected $code = 31;
    protected $message = "Field %s is not Attribute nor Relationship";

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
