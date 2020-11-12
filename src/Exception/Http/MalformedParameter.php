<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Http;


use JSONAPI\Exception\HasParameter;

/**
 * Class MalformedParameter
 *
 * @package JSONAPI\Exception\Http
 */
class MalformedParameter extends BadRequest implements HasParameter
{
    /**
     * @var string
     */
    protected $message = "Query parameter %s is malformed.";

    /**
     * @var string
     */
    private string $parameter;


    public function __construct(string $parameter)
    {
        parent::__construct(sprintf($this->message, $parameter));
        $this->parameter = $parameter;
    }

    public function getParameter(): string
    {
        return $this->parameter;
    }
}
