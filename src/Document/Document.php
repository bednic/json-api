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
     * \JSONAPI\Document\Resource|\JSONAPI\Document\Resource[]Resource|Resource[]
     */
    private $data;
    /**
     * @var Error[]
     */
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
     * @param Resource|Resource[] $data
     * @param array               $includes
     * @param array               $links
     * @param array               $meta
     * @return Document
     */
    public static function create($data, array $includes = [], array $links = [], array $meta = [])
    {
        $instance = new static();
        $instance->setData($data);
        $instance->setIncludes(new ArrayCollection($includes));
        $instance->meta = new ArrayCollection($meta);
        $instance->links = new ArrayCollection($links);
        return $instance;

    }

    /**
     * @return \JSONAPI\Document\Resource|\JSONAPI\Document\Resource[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \JSONAPI\Document\Resource|\JSONAPI\Document\Resource[] $data
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

    /**
     * @param string $key
     * @param string $link
     */
    public function addLink(string $key, string $link): void
    {
        $this->links->set($key, $link);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getLink(string $key): ?string
    {
        return $this->links->get($key);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addMeta(string $key, string $value)
    {
        $this->meta->set($key, $value);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getMeta(string $key): ?string
    {
        return $this->meta->get($key);
    }

    /**
     * @param Error $error
     */
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
