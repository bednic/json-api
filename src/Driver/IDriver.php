<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 15:57
 */

namespace JSONAPI\Driver;

use JSONAPI\ClassMetadata;

/**
 * Interface IDriver
 * @package JSONAPI\Driver
 */
interface IDriver
{
    /**
     * @param string $className
     * @return ClassMetadata|null
     */
    public function getClassMetadata(string $className): ?ClassMetadata;
}
