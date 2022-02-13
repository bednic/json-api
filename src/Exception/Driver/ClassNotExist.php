<?php

declare(strict_types=1);

namespace JSONAPI\Exception\Driver;

/**
 * Class ClassNotExist
 *
 * @package JSONAPI\Exception\DriverInterface
 */
class ClassNotExist extends DriverException
{
    /**
     * @var int
     */
    protected $code = 533;
    /**
     * @var string
     */
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
