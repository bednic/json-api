<?php

namespace JSONAPI;

/**
 * Interface JsonDeserializable
 * Marks class that can be initialized from json data
 *
 * @package JSONAPI
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
