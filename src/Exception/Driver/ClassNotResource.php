<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

/**
 * Class ClassNotResource
 *
 * @package JSONAPI\Exception\Driver
 */
class ClassNotResource extends DriverException
{
    /**
     * @var int
     */
    protected $code = 544;
    /**
     * @var string
     */
    protected $message = "Class %s is not Resource.";

    /**
     * ClassNotResource constructor.
     *
     * @param string $className
     */
    public function __construct(string $className)
    {
        $message = sprintf($this->message, $className);
        parent::__construct($message);
    }
}
