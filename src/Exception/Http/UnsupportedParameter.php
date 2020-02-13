<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Http;

/**
 * Class UnsupportedParameter
 *
 * @package JSONAPI\Exception\Http
 */
class UnsupportedParameter extends BadRequest
{
    /**
     * @var string
     */
    private string $parameter;

    /**
     * UnsupportedParameter constructor.
     *
     * @param string $parameter
     */
    public function __construct(string $parameter)
    {
        parent::__construct(parent::getMessage(), parent::getCode());
        $this->parameter = $parameter;
    }

    /**
     * @return string
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }
}
