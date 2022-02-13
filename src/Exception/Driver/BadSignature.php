<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

/**
 * Class BadMethodSignature
 *
 * @package JSONAPI\Exception\Driver
 */
class BadSignature extends DriverException
{
    /**
     * @var int
     */
    protected $code = 532;
    /**
     * @var string
     */
    protected $message = "Method or property %s on class %s does not seem valid. Please check:
    1) If it is getter, there should be method return type signature.
    2) If it is setter, there should be one and only one method parameter.
    3) If it is property, there should be its data type.";

    /**
     * BadMethodSignature constructor.
     *
     * @param string $name
     * @param string $className
     */
    public function __construct(string $name, string $className)
    {
        $message = sprintf($this->message, $name, $className);
        parent::__construct($message);
    }
}
