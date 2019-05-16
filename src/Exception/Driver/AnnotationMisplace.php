<?php


namespace JSONAPI\Exception\Driver;


class AnnotationMisplace extends DriverException
{
    protected $code = 11;
    protected $message = "Annotation %s on method MUST be on getter. 
    Method %s on resource %s doesn't seems like getter.";

    public function __construct(string $annotationClass, string $methodName, string $className)
    {
        $message = sprintf($this->message, $annotationClass, $methodName, $className);
        parent::__construct($message);
    }
}
