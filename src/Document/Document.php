<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:48
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\DocumentException;
use JSONAPI\LinkProvider;
use JsonSerializable;

/**
 * Class Document
 * @package JSONAPI\Document
 */
class Document implements JsonSerializable
{
    const MEDIA_TYPE = "application/vnd.api+json";

    const VERSION = "1.0";

    /**
     * @var Resource|Resource[]
     */
    private $data;

    private $errors = null;

    /**
     * @var ArrayCollection
     */
    private $meta;

    /**
     * @var ArrayCollection
     */
    private $links;

    /**
     * @var ArrayCollection
     */
    private $included;

    /**
     * Document constructor.
     */
    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->meta = new ArrayCollection();
        $this->included = new ArrayCollection();
    }

    /**
     * @param Resource | Resource[] $data
     * @param array                 $includes
     * @return Document
     */
    public static function create($data, array $includes)
    {
        $instance = new static();
        $instance->setData($data);
        $instance->setIncludes(new ArrayCollection($includes));
        return $instance;

    }

    /**
     * @param Resource | Resource[] $data
     */
    public function setData($data)
    {
        $this->data = $data;
        [$key, $link] = LinkProvider::createPrimaryDataLink($data);
        $this->addLink($key, $link);
    }

    /**
     * @return ArrayCollection
     */
    public function getIncludes()
    {
        return $this->included;
    }

    /**
     * @param ArrayCollection $includes
     */
    public function setIncludes(ArrayCollection $includes): void
    {
        $this->included = $includes;
    }

    public function addLink(string $key, string $link): void
    {
        $this->links->set($key, $link);
    }

    public function getLink($key): string
    {
        return $this->links->get($key);
    }

    public function addMeta(string $key,string $value)
    {
        $this->meta->set($key, $value);
    }

    public function addError(Error $error)
    {
        $this->errors[] = $error;
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
        $ret["jsonapi"] = ["version" => self::VERSION];
        if (!$this->meta->isEmpty()) {
            $ret["meta"] = $this->meta->toArray();
        }
        if ($this->data && $this->errors) {
            throw new DocumentException("Non-valid document. Data AND Errors are set. Only Data XOR Errors are allowed");
        }

        if ($this->errors) {
            $ret["errors"] = $this->errors;
        } else {
            $ret["data"] = $this->data;
        }

        if (!$this->links->isEmpty()) {
            $ret["links"] = $this->links->toArray();
        }
        if (!$this->included->isEmpty()) {
            $ret["included"] = $this->included->toArray();
        }
        return $ret;
    }

    /**
     * @return false|string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
