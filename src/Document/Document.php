<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace OpenAPI\Document;

use OpenAPI\EncoderOptions;
use OpenAPI\Exception\DocumentException;
use OpenAPI\Filter;


class Document implements \JsonSerializable
{

    private $data;
    private $errors = null;
    private $meta = null;
    private $jsonapi = [
        "version" => "1.0"
    ];
    private $links = [];
    private $included = null;

    private $filter;

    /**
     * Document constructor.
     */
    public function __construct()
    {
        $this->filter = new Filter();
        $this->links = [
            Links::SELF => $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
        ];
    }

    /**
     * @param Resource|Resource[] $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function setIncludes($includes)
    {
        $this->included = $includes;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @throws DocumentException
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $ret = [];
        if ($this->jsonapi) {
            $ret["jsonapi"] = $this->jsonapi;
        }
        if ($this->meta) {
            $ret["meta"] = $this->meta;
        }
        if ($this->data && $this->errors) {
            throw new DocumentException("Non-valid document. Data AND Errors are set. Only Data XOR Errors are allowed");
        }

        if ($this->errors) {
            $ret["errors"] = $this->errors;
        } else {
            $ret["data"] = $this->data;
        }

        if ($this->links) {
            $ret["links"] = $this->links;
        }
        if ($this->included) {
            $ret["included"] = $this->included;
        }
        return $ret;
    }
}
