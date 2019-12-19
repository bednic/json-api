<?php

namespace JSONAPI\Exception\Driver;

/**
 * Class MethodNotExist
 *
 * @package JSONAPI\Exception\Driver
 */
class MethodNotExist extends DriverException
{
    protected $code = 535;
    protected $message = "Method %s does not exist on class %s";

    public function __construct(string $methodName, string $className)
    {
        $message = sprintf($this->message, $methodName, $className);
        parent::__construct($message);
    }
}
