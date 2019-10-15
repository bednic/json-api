<?php

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JsonSerializable;

/**
 * Class Meta
 *
 * @package JSONAPI\Document
 */
class Meta implements JsonSerializable
{

    /**
     * @var array
     */
    private $properties;

    /**
     * Meta constructor.
     *
     * @param array $properties
     *
     * @example [
     *          'key' => 'value',
     *          ...
     * ]
     */
    public function __construct(array $properties = [])
    {
        $this->properties = $properties;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function setProperty(string $key, $value): void
    {
        if (!preg_match("/(^[a-zA-Z0-9])(([a-zA-Z-_]+)([a-zA-Z0-9]))?$/", $key)) {
            throw new ForbiddenCharacter($key);
        }
        if (!in_array(gettype($value), ["boolean", "integer", "double", "string", "array", "NULL", "object"])) {
            throw new ForbiddenDataType(gettype($value));
        }
        $this->properties[$key] = $value;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->properties) === 0;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->properties;
    }
}
