<?php

declare(strict_types=1);

namespace JSONAPI\Document;

use JSONAPI\Data\Collection;
use JSONAPI\Exception\Document\AlreadyInUse;
use JSONAPI\Exception\Document\AttributeNotExist;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Exception\Document\RelationshipNotExist;

/**
 * Class ResourceObject
 *
 * @package JSONAPI\Document
 */
final class ResourceObject extends ResourceObjectIdentifier implements HasLinks, PrimaryData
{
    use LinksExtension;

    /**
     * @param Attribute $attribute
     *
     * @throws AlreadyInUse
     */
    public function addAttribute(Attribute $attribute): void
    {
        $this->addField($attribute);
    }

    /**
     * @param Relationship $relationship
     *
     * @throws AlreadyInUse
     */
    public function addRelationship(Relationship $relationship): void
    {
        $this->addField($relationship);
    }

    /**
     * Returns Attribute value
     *
     * @param string $key
     *
     * @return mixed
     * @throws AttributeNotExist
     */
    public function getAttribute(string $key): mixed
    {
        if (!$this->hasAttribute($key)) {
            throw new AttributeNotExist($key);
        }
        return $this->fields->get($key)->getData();
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        $ret = [];
        /** @var Attribute $attribute */
        foreach ($this->attributes() as $attribute) {
            $ret[$attribute->getKey()] = $attribute->getData();
        }
        return $ret;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return $this->attributes()->offsetExists($key);
    }

    /**
     * @return Collection
     */
    private function attributes(): Collection
    {
        return $this->fields->filter(
            function ($element) {
                return $element instanceof Attribute;
            }
        );
    }

    /**
     * Reruns Relationship value
     *
     * @param string $key
     *
     * @return ResourceObjectIdentifier|ResourceObjectIdentifier[]
     * @throws RelationshipNotExist
     */
    public function getRelationship(string $key): ResourceObjectIdentifier | array
    {
        if (!$this->hasRelationship($key)) {
            throw new RelationshipNotExist($key);
        }
        return $this->fields->get($key)->getData();
    }

    /**
     * @return array<string, mixed>
     */
    public function getRelationships(): array
    {
        $ret = [];
        /** @var Relationship $relationship */
        foreach ($this->relationships() as $relationship) {
            $ret[$relationship->getKey()] = $relationship->getData();
        }
        return $ret;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasRelationship(string $key): bool
    {
        return $this->relationships()->offsetExists($key);
    }

    /**
     * @return Collection
     */
    private function relationships(): Collection
    {
        return $this->fields->filter(
            function ($element) {
                return $element instanceof Relationship;
            }
        );
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return object data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): object
    {
        $ret = parent::jsonSerialize();
        if ($this->attributes()->count() > 0) {
            $ret->attributes = (object)$this->attributes()->toArray();
        }
        if ($this->relationships()->count() > 0) {
            $ret->relationships = (object)$this->relationships()->toArray();
        }
        if ($this->hasLinks()) {
            $ret->links = (object)$this->getLinks();
        }
        return $ret;
    }
}
