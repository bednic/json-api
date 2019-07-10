<?php


namespace JSONAPI;


interface JsonDeserializable
{
    /**
     * @param array $json
     * @return static
     */
    public static function jsonDeserialize(array $json);
}
