<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 16.04.2019
 * Time: 14:35
 */

namespace JSONAPI\Document;


use JSONAPI\Exception\EncoderException;

class Attribute implements \JsonSerializable
{
    private $name;
    private $value;

    /**
     * Attribute constructor.
     * @param string                                             $name
     * @param boolean | integer | double | string | array | null $value
     * @throws EncoderException
     */
    public function __construct(string $name, $value)
    {
        if (!in_array(gettype($value), ["boolean", "integer", "double", "string", "array", "NULL"])) {
            throw new EncoderException("Attribute value type is not supported");
        }
        if (!preg_match("/[a-zA-Z0-9-_]/", $name)) {
            throw new EncoderException("Attribute name character violation.");
        }
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
