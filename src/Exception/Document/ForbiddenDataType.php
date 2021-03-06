<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

/**
 * Class ForbiddenDataType
 *
 * @package JSONAPI\Exception\Document
 */
class ForbiddenDataType extends DocumentException
{
    protected $code = 522;
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
