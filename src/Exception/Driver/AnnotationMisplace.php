<?php

namespace JSONAPI\Exception\Driver;

class AnnotationMisplace extends DriverException
{
    protected $code = 531;
    protected $message = "Annotation on method MUST be on getter.
    It should start with 'get', 'is' or 'has' and have some return type.
    Method %s on resource %s doesn't seems like getter.";

    public function __construct(string $methodName, string $className)
    {
        $message = sprintf($this->message, $methodName, $className);
        parent::__construct($message);
    }
}
