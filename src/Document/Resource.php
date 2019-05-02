<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:57
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Exception\DocumentException;

/**
 * Class Resource
 *
 * @package JSONAPI\Document
 */
class Resource extends ResourceIdentifier
{
    /**
     * @var ArrayCollection | Attribute[]
     */
    private $attributes;
    /**
     * @var ArrayCollection | Relationship[]
     */
    private $relationships;

    /**
     * @param ResourceIdentifier $resourceIdentifier
     */

    public function __construct(ResourceIdentifier $resourceIdentifier)
    {
        parent::__construct($resourceIdentifier->type, $resourceIdentifier->id);
        $this->attributes = new ArrayCollection();
        $this->relationships = new ArrayCollection();
    }

    /**
     * @param Attribute $attribute
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes->set($attribute->getKey(), $attribute->getValue());
    }

    /**
     * Function return attribute value by attribute name
     *
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        return $this->attributes->get($name);
    }

    /**
     * @param Relationship $relationship
     */
    public function addRelationship(Relationship $relationship)
    {
        $this->relationships->set($relationship->getName(), $relationship);
    }

    /**
     * @param string $name
     * @return Relationship|null
     */
    public function getRelationship(string $name): ?Relationship
    {
        return $this->relationships->get($name);
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
        $ret = parent::jsonSerialize();
        if (!$this->attributes->isEmpty()) {
            $ret['attributes'] = $this->attributes->toArray();
        }
        if (!$this->relationships->isEmpty()) {
            $ret['relationships'] = $this->relationships->toArray();
        }
        return $ret;
    }
}
