<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 16.04.2019
 * Time: 14:35
 */

namespace JSONAPI\Document;


use JSONAPI\Exception\DocumentException;

class Attribute extends KVStore
{
    /**
     * Attribute constructor.
     *
     * @param string                                             $key
     * @param boolean | integer | double | string | array | null $value
     * @throws DocumentException
     */
    public function __construct(string $key, $value)
    {
        if (!in_array(gettype($value), ["boolean", "integer", "double", "string", "array", "NULL", "object"])) {
            throw new DocumentException("Attribute value type is not supported",
                DocumentException::DOCUMENT_FORBIDDEN_VALUE_TYPE);
        }
        if (!preg_match("/[a-zA-Z0-9-_]/", $key)) {
            throw new DocumentException("Attribute name character violation.",
                DocumentException::DOCUMENT_FORBIDDEN_CHARACTER);
        }
        parent::__construct($key, $value);
    }

}
