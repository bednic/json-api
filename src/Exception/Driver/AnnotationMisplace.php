<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

/**
 * Class AnnotationMisplace
 *
 * @package JSONAPI\Exception\Driver
 */
class AnnotationMisplace extends DriverException
{
    protected $code = 531;
    protected $message = "Annotation on method MUST be on getter.
    It should start with 'get', 'is' or 'has' and have some return type.
    Method %s on resource %s doesn't seems like getter.";

    /**
     * AnnotationMisplace constructor.
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
