<?php

namespace JSONAPI\Driver;

use JSONAPI\Exception\Driver\DriverException;
use JSONAPI\Metadata\ClassMetadata;

/**
 * Interface DriverInterface
 *
 * @package JSONAPI\DriverInterface
 */
interface DriverInterface
{
    /**
     * @param string $className
     *
     * @return ClassMetadata
     * @throws DriverException
     */
    public function getClassMetadata(string $className): ClassMetadata;
}
