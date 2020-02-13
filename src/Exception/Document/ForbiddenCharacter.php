<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Document;

/**
 * Class ForbiddenCharacter
 *
 * @package JSONAPI\Exception\Http
 */
class ForbiddenCharacter extends DocumentException
{
    protected $code = 521;
    protected $message = "Parameter %s contains forbidden character(s).";

    /**
     * ForbiddenCharacter constructor.
     *
     * @param string $parameterName
     */
    public function __construct(string $parameterName)
    {
        $message = sprintf($this->message, $parameterName);
        parent::__construct($message);
    }
}
