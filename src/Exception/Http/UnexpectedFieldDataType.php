<?php

/**
 * Created by lasicka@logio.cz
 * at 26.05.2021 16:04
 */

declare(strict_types=1);

namespace JSONAPI\Exception\Http;

/**
 * Class UnexpectedFieldDataType
 *
 * @package JSONAPI\Exception\Http
 */
class UnexpectedFieldDataType extends BadRequest
{
    /**
     * @var string
     */
    protected $message = "Field '%s' value has unexpected data type [%s], expected [%s].";

    public function __construct(string $field, string $provided, string $expected)
    {
        parent::__construct(sprintf($this->message, $field, $provided, $expected));
    }
}
