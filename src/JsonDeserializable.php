<?php

namespace JSONAPI;

interface JsonDeserializable
{
    /**
     * @param mixed $json
     *
     * @return static
     */
    public static function jsonDeserialize($json);
}
