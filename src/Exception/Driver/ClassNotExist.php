<?php

namespace JSONAPI\Exception\Driver;

/**
 * Class ClassNotExist
 *
 * @package JSONAPI\Exception\Driver
 */
class ClassNotExist extends DriverException
{
    protected $code = 12;
    protected $message = "Class %s does not exist.";

    /**
     * ClassNotExist constructor.
     *
     * @param string $className
     */
    public function __construct(string $className)
    {
        $message = sprintf($this->message, $className);
        parent::__construct($message);
    }
}
