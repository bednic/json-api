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
     * @var array|null
     */
    private ?array $trace = null;

    private function __construct()
    {
        // Instance should be created only with specific properties
    }

    /**
     * @param string $pointer
     *
     * @return static
     */
    public static function pointer(string $pointer): self
    {
        $static          = new static();
        $static->pointer = $pointer;
        return $static;
    }

    /**
     * @param string $parameter
     *
     * @return static
     */
    public static function parameter(string $parameter)
    {
        $static            = new static();
        $static->parameter = $parameter;
        return $static;
    }

    /**
     * @param string $line
     * @param        $trace
     *
     * @return static
     */
    public static function internal(string $line, $trace)
    {
        $static       = new static();
        $static->line = $line;
        $steps        = preg_split('/#[0-9]+ /', $trace);
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
