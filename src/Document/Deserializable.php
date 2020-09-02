<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use stdClass;

/**
 * Interface JsonDeserializable
 * Marks class that can be initialized from json data
 *
 * @package JSONAPI
 */
interface Deserializable
{
    /**
     * @param stdClass|array $json
     *
     * @return static
     */
    public static function jsonDeserialize($json): self;
}
