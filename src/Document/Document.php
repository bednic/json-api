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

/**
 * Class Document
 * @package JSONAPI\Document
 */
class Document implements \JsonSerializable
{
    const MEDIA_TYPE = "application/vnd.api+json";

    private $linkProvider;


    private $data;

    private $errors = null;
    /**
     * @var ArrayCollection
     */
    private $meta;
    private $jsonapi = [
        "version" => "1.0"
    ];
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
     * @param LinkProvider $linkProvider
     */
    public function __construct(LinkProvider $linkProvider)
    {
        $this->linkProvider = $linkProvider;
        $this->links = new ArrayCollection();
        $this->meta = new ArrayCollection();
        $this->included = new ArrayCollection();
        $this->addLink(...$linkProvider->createPrimaryDataLink());
    }

    /**
     * @param \JSONAPI\Document\Resource | \JSONAPI\Document\Resource[] | null $data
     */
    public function setData($data)
    {
        $this->data = $data;
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
    public function setIncludes(ArrayCollection $includes)
    {
        $this->included = $includes;
    }

    public function addLink($key, $link)
    {
        $this->links->set($key, $link);
    }

    public function getLink($key)
    {
        return $this->links->get($key);
    }

    public function addMeta($key, $value)
    {
        $this->meta->set($key, $value);
    }

    public function addError($error)
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
        if ($this->jsonapi) {
            $ret["jsonapi"] = $this->jsonapi;
        }
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
}
