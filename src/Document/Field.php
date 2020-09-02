<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;

/**
 * Class FieldMetadata
 *
 * @package JSONAPI\Document
 */
abstract class Field implements Serializable
{

    /**
     * @var string
     */
    protected string $key;
    /**
     * @var mixed
     */
    protected $data;

    /**
     * FieldMetadata constructor.
     *
     * @param string $key
     *
     * @throws ForbiddenCharacter
     */
    public function __construct(string $key)
    {
        $this->setKey($key);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @throws ForbiddenCharacter
     */
    protected function setKey(string $key): void
    {
        if (!preg_match("/(^[a-zA-Z0-9])(([a-zA-Z0-9-_]*)([a-zA-Z0-9]))$/", $key)) {
            throw new ForbiddenCharacter($key);
        }
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @throws ForbiddenDataType
     */
    protected function setData($data): void
    {
        if (!in_array(gettype($data), ["boolean", "integer", "double", "string", "array", "NULL", "object"])) {
            throw new ForbiddenDataType(gettype($data));
        }
        $this->data = $data;
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
        return $this->getData();
    }
}
