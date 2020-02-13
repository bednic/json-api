<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

/**
 * Class MethodNotExist
 *
 * @package JSONAPI\Exception\AnnotationDriver
 */
class PropertyNotExist extends DriverException
{
    protected $code = 536;
    protected $message = "Property %s does not exist on class %s";

    /**
     * PropertyNotExist constructor.
     *
     * @param string $propertyName
     * @param string $className
     */
    public function __construct(string $propertyName, string $className)
    {
        $message = sprintf($this->message, $propertyName, $className);
        parent::__construct($message);
    }
}
