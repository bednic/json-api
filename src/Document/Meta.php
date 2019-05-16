<?php


namespace JSONAPI\Document;

use JsonSerializable;

/**
 * Class Meta
 *
 * @package JSONAPI\Document
 */
class Meta implements JsonSerializable
{

    /**
     * @var Field[]
     */
    private $fields;

    /**
     * Meta constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $key => $value) {
            $this->addField($key, $value);
        }
    }

    /**
     * @param $key
     * @param $value

     */
    public function addField($key, $value): void
    {
        $this->fields[$key] = $value;
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
        return $this->fields;
    }
}
