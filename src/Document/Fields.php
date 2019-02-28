<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 11.02.2019
 * Time: 12:49
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Fields
 * @package JSONAPI\Document
 */
class Fields implements \JsonSerializable
{
    /**
     * @var ArrayCollection
     */
    private $attributes;
    /**
     * @var ArrayCollection
     */
    private $relationships;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
        $this->relationships = new ArrayCollection();
    }

    public function addField($name, $value)
    {
        if ($value instanceof Relationships) {

            $this->relationships->set($name, $value);
        } else {
            $this->attributes->set($name, $value);
        }

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
        $ret = [
            'attributes' => $this->getAttributes()->toArray()
        ];
        if (!$this->relationships->isEmpty()) {
            $ret['relationships'] = $this->getRelationships()->toArray();
        }
        return $ret;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getRelationships()
    {
        return $this->relationships;
    }

}
