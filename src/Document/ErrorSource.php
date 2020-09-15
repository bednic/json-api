<?php

declare(strict_types=1);

namespace JSONAPI\Document;

/**
 * Class ErrorSource
 *
 * @package JSONAPI\Document
 */
class ErrorSource implements Serializable
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

    /**
     * @var string|null
     */
    private ?string $line = null;

    /**
     * @var array<string>|null
     */
    private ?array $trace = null;

    private function __construct()
    {
        // Instance should be created only with specific properties
    }

    /**
     * @param string $pointer
     *
     * @return ErrorSource
     */
    public static function pointer(string $pointer): self
    {
        $static          = new self();
        $static->pointer = $pointer;
        return $static;
    }

    /**
     * @param string $parameter
     *
     * @return ErrorSource
     */
    public static function parameter(string $parameter): self
    {
        $static            = new self();
        $static->parameter = $parameter;
        return $static;
    }

    /**
     * @param string $line
     * @param string $trace
     *
     * @return ErrorSource
     */
    public static function internal(string $line, string $trace): self
    {
        $static       = new self();
        $static->line = $line;
        $steps        = preg_split('/#[0-9]+ /', $trace) !== false ? preg_split('/#[0-9]+ /', $trace) : [];
        array_shift($steps);
        $static->trace = $steps;
        return $static;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $ret = [];
        if ($this->parameter) {
            $ret['parameter'] = $this->parameter;
        } elseif ($this->pointer) {
            $ret['pointer'] = $this->pointer;
        } else {
            $ret['line']  = $this->line;
            $ret['trace'] = $this->trace;
        }
        return $ret;
    }
}
