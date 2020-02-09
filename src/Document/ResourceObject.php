<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 14:57
 */

namespace JSONAPI\Document;

use JSONAPI\Exception\Document\ReservedWord;
use JSONAPI\Exception\Metadata\AttributeNotFound;
use JSONAPI\Exception\Metadata\RelationNotFound;
use JSONAPI\LinksTrait;

/**
 * Class ResourceObject
 *
 * @package JSONAPI\Document
 */
final class ResourceObject extends ResourceObjectIdentifier implements HasLinks, PrimaryData
{
    use LinksTrait;

    /**
     * @param Attribute $attribute
     *
     * @throws ReservedWord
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->addField($attribute);
    }

    /**
     * Function return AttributeMetadata or null if doesn't exist
     *
     * @param string $key
     *
     * @return mixed
     * @throws AttributeNotFound
     */
    public function getAttribute(string $key)
    {
        if (!$this->fields->containsKey($key) || !($this->fields->get($key) instanceof Attribute)) {
            throw new AttributeNotFound($key, $this->getType());
        }
        return $this->fields->get($key)->getData();
    }

    /**
     * @param Relationship $relationship
     *
     * @throws ReservedWord
     */
    public function addRelationship(Relationship $relationship)
    {
        $this->addField($relationship);
    }

    /**
     * @param string $key
     *
     * @return ResourceObjectIdentifier|ResourceObjectIdentifier[]
     * @throws RelationNotFound
     */
    public function getRelationship(string $key)
    {
        if (!$this->fields->containsKey($key) || !($this->fields->get($key) instanceof Relationship)) {
            throw new RelationNotFound($key, $this->getType());
        }
        return $this->fields->get($key)->getData();
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->fields->filter(function ($element) {
            return $element instanceof Attribute;
        })->map(function ($element) {
            /** @var Attribute $element */
            return $element;
        })->toArray();
    }

    /**
     * @return Relationship[]
     */
    public function getRelationships(): array
    {
        return $this->fields->filter(function ($element) {
            return $element instanceof Relationship;
        })->map(function ($element) {
            /** @var Relationship $element */
            return $element;
        })->toArray();
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
        if ($this->hasLinks()) {
            $ret['links'] = $this->getLinks();
        }
        return $ret;
    }
}
