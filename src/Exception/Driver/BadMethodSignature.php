<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

/**
 * Class BadMethodSignature
 *
 * @package JSONAPI\Exception\Driver
 */
class BadMethodSignature extends DriverException
{
    protected $code = 532;
    protected $message = "Method %s on class %s does not seem valid. Please check:
    1) If it is getter, there should be method return type signature.
    2) If it is setter, there should be one and only one method parameter.";

    /**
     * BadMethodSignature constructor.
     *
     * @param string $methodName
     * @param string $className
     */
    public function __construct(string $methodName, string $className)
    {
        $message = sprintf($this->message, $methodName, $className);
        parent::__construct($message);
    }
}
