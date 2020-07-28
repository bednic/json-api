<?php

declare(strict_types=1);

namespace JSONAPI;

/**
 * Interface JsonDeserializable
 * Marks class that can be initialized from json data
 *
 * @package JSONAPI
 * @deprecated use \Tools\JSON\JsonDeserializable
 * @see https://gitlab.com/bednic/tools/-/blob/master/src/JSON/JsonDeserializable.php
 * @version 5.1.1
 */
interface JsonDeserializable
{
    /**
     * @param mixed $json
     *
     * @return static
     */
    public static function jsonDeserialize($json);
}
