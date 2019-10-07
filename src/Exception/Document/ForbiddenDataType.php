<?php

namespace JSONAPI\Exception\Document;

/**
 * Class ForbiddenDataType
 *
 * @package JSONAPI\Exception\Document
 */
class ForbiddenDataType extends BadRequest
{
    protected $message = "Assigned data has forbidden data type %s.";

    /**
     * ForbiddenDataType constructor.
     *
     * @param string $dataType
     */
    public function __construct(string $dataType)
    {
        $message = sprintf($this->message, $dataType);
        parent::__construct($message);
    }
}
