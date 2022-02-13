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
    /**
     * @var int
     */
    protected $code = 522;
    /**
     * @var string
     */
    protected $message = "Assigned data to %s has forbidden data type %s.";

    /**
     * ForbiddenDataType constructor.
     *
     * @param string $dataType
     */
    public function __construct(string $propName, string $dataType)
    {
        $message = sprintf($this->message, $propName, $dataType);
        parent::__construct($message);
    }
}
