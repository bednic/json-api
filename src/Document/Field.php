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

    public const KEY_REGEX = "/^([a-zA-Z0-9]+)([a-zA-Z-0-9_]*[a-zA-Z-0-9])?$/";
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
        if (!preg_match(Field::KEY_REGEX, $key)) {
            throw new ForbiddenCharacter($key);
        }
        $this->key = $key;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): mixed
    {
        return $this->getData();
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    protected function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
