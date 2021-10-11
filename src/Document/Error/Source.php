<?php

declare(strict_types=1);

namespace JSONAPI\Document\Error;

use JSONAPI\Document\Serializable;

/**
 * Class ErrorSource
 *
 * @package JSONAPI\Document
 */
final class Source implements Serializable
{
    /**
     * JSON Pointer [RFC6901] to the associated entity in the request document
     *
     * @link https://tools.ietf.org/html/rfc6901
     * @var string|null
     */
    private ?string $pointer = null;

    /**
     * A string indicating which URI query parameter caused the error
     *
     * @var string|null
     */
    private ?string $parameter = null;

    private function __construct()
    {
        // Instance should be created only with specific properties
    }

    /**
     * @param string $pointer
     *
     * @return Source
     */
    public static function pointer(string $pointer): self
    {
        $static = new self();
        $static->pointer = $pointer;
        return $static;
    }

    /**
     * @param string $parameter
     *
     * @return Source
     */
    public static function parameter(string $parameter): self
    {
        $static = new self();
        $static->parameter = $parameter;
        return $static;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        $ret = [];
        if (!is_null($this->parameter)) {
            $ret['parameter'] = $this->parameter;
        } else {
            $ret['pointer'] = $this->pointer;
        }
        return (object)$ret;
    }
}
