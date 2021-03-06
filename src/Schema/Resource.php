<?php

declare(strict_types=1);

namespace JSONAPI\Schema;

/**
 * Interface ResourceDescriptor
 *
 * @package JSONAPI\Schema
 */
interface Resource
{
    /**
     * Returns schema for resource
     *
     * @return ResourceSchema
     */
    public static function getSchema(): ResourceSchema;
}
