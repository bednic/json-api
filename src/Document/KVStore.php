<?php


namespace JSONAPI\Document;


abstract class KVStore implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $key;
    /**
     * @var mixed
     */
    protected $value;

    /**
     * KVStore constructor.
     *
     * @param string $key
     * @param        $value
     */
    public function __construct(string $key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->getValue();
    }

}
