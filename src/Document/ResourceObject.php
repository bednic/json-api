<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:57
 */

namespace JSONAPI\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\LinksTrait;

/**
 * Class ResourceObject
 *
 * @package JSONAPI\Document
 */
class ResourceObject extends ResourceObjectIdentifier implements HasLinks
{
    use LinksTrait;

    /**
     * @var Collection|Field[]
     */
    private Collection $fields;

    /**
     * ResourceObject constructor.
     *
     * @param ResourceObjectIdentifier $resourceIdentifier
     */
    public function __construct(ResourceObjectIdentifier $resourceIdentifier)
    {
        parent::__construct($resourceIdentifier->type, $resourceIdentifier->id);
        $this->fields = new ArrayCollection();
    }

    /**
     * @param Attribute $attribute
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->fields->set($attribute->getKey(), $attribute);
    }

    /**
     * Function return Attribute or null if doesn't exist
     *
     * @param string $key
     *
     * @return Attribute|null
     */
    public function getAttribute(string $key): ?Attribute
    {
        $attribute = $this->fields->get($key);
        if ($attribute instanceof Attribute) {
            return $this->fields->get($key);
        }
        return null;
    }

    /**
     * @param Relationship $relationship
     */
    public function addRelationship(Relationship $relationship)
    {
        $this->fields->set($relationship->getKey(), $relationship);
    }

    /**
     * Returns Relationship or null if doesn't exist
     *
     * @param string $key
     *
     * @return Relationship | null
     */
    public function getRelationship(string $key): ?Relationship
    {
        $relationship = $this->fields->get($key);
        if ($relationship instanceof Relationship) {
            return $this->fields->get($key);
        }
        return null;
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
        if ($this->getAttributes()) {
            $ret['attributes'] = $this->getAttributes();
        }
        if ($this->getRelationships()) {
            $ret['relationships'] = $this->getRelationships();
        }
        if($this->hasLinks()){
            $ret['links'] = $this->getLinks();
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->fields->filter(function ($element) {
            return $element instanceof Attribute;
        })->toArray();
    }

    /**
     * @return array
     */
    public function getRelationships(): array
    {
        return $this->fields->filter(function ($element) {
            return $element instanceof Relationship;
        })->toArray();
    }
}
