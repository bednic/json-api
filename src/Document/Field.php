<?php


namespace JSONAPI\Document;


use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JsonSerializable;

/**
 * Class Field
 *
 * @package JSONAPI\Document
 */
abstract class Field implements JsonSerializable
{
    /**
     * @var string
     */
    protected $key;
    /**
     * @var mixed
     */
    protected $data;

    /**
     * Field constructor.
     *
     * @param string $key
     * @param        $data
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function __construct(string $key, $data)
    {
        $this->setKey($key);
        $this->setData($data);
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
     * @throws ForbiddenCharacter
     */
    public function setKey(string $key)
    {
        if (!preg_match("/[a-zA-Z0-9-_]/", $key)) {
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
     * @param $data
     * @throws ForbiddenDataType
     */
    public function setData($data)
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
