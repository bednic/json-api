<?php

namespace JSONAPI\Exception\Driver;

class ClassNotResource extends DriverException
{
    protected $code = 544;
    protected $message = "Class %s is not Resource.";

    public function __construct(string $className)
    {
        $message = sprintf($this->message, $className);
        parent::__construct($message);
    }
}
