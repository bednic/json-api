<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 15:57
 */

namespace OpenAPI\Driver;


use OpenAPI\ClassMetadata;
use OpenAPI\Exception\InvalidObjectException;

interface IDriver
{
    /**
     * @param string $className
     * @return ClassMetadata | null if no Resource object for $classname found.
     * @throws InvalidObjectException
     */
    public function getClassMetadata(string $className): ?ClassMetadata;
}
