<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use Doctrine\Common\Collections\Collection;
use JSONAPI\Exception\Document\FieldNotSet;
use JSONAPI\Exception\Document\ReservedWord;
use JSONAPI\Helper\LinksTrait;

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
     * @param Relationship $relationship
     *
     * @throws ReservedWord
     */
    public function addRelationship(Relationship $relationship)
    {
        $this->addField($relationship);
    }

    /**
     * Returns Attribute value
     *
     * @param string $key
     *
     * @return mixed
     * @throws FieldNotSet
     */
    public function getAttribute(string $key)
    {
        if (!$this->hasAttribute($key)) {
            throw new FieldNotSet($key);
        }
        return $this->fields->get($key)->getData();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return $this->getAttributes()->containsKey($key);
    }

    /**
     * Reruns Relationship value
     *
     * @param string $key
     *
     * @return ResourceObjectIdentifier|ResourceObjectIdentifier[]
     * @throws FieldNotSet
     */
    public function getRelationship(string $key)
    {
        if (!$this->hasRelationship($key)) {
            throw new FieldNotSet($key);
        }
        return $this->fields->get($key)->getData();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasRelationship(string $key): bool
    {
        return $this->getRelationships()->containsKey($key);
    }

    /**
     * @return Collection
     */
    private function getAttributes(): Collection
    {
        return $this->fields->filter(function ($element) {
            return $element instanceof Attribute;
        });
    }

    /**
     * @return Collection
     */
    private function getRelationships(): Collection
    {
        return $this->fields->filter(function ($element) {
            return $element instanceof Relationship;
        });
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
        if ($this->getAttributes()->count() > 0) {
            $ret['attributes'] = $this->getAttributes()->toArray();
        }
        if ($this->getRelationships()->count() > 0) {
            $ret['relationships'] = $this->getRelationships()->toArray();
        }
        if ($this->hasLinks()) {
            $ret['links'] = $this->getLinks();
        }
        return $ret;
    }
}
