<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class Meta
 *
 * @package JSONAPI\Document
 */
final class Meta implements Serializable
{

    /**
     * @var array
     */
    private array $properties = [];

    /**
     * Meta constructor.
     *
     * @param array $properties
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     * @example [
     *          'key' => 'value',
     *          ...
     * ]
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function setProperty(string $key, mixed $value): void
    {
        if (!preg_match(Field::KEY_REGEX, $key)) {
            throw new ForbiddenCharacter($key);
        }
        if (!in_array(gettype($value), ["boolean", "integer", "double", "string", "array", "NULL", "object"])) {
            throw new ForbiddenDataType($key, gettype($value));
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
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return $this->properties;
    }
}
