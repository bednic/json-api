<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 13:03
 */

namespace JSONAPI\Metadata;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Annotation;
use JSONAPI\Annotation\Attribute;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Meta;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;
use JSONAPI\Exception\Metadata\AttributeNotFound;
use JSONAPI\Exception\Metadata\MetaNotFound;
use JSONAPI\Exception\Metadata\RelationNotFound;

/**
 * Class ClassMetadata
 *
 * @package JSONAPI
 */
final class ClassMetadata
{
    /**
     * @var string
     */
    private string $className;

    /**
     * @var Id
     */
    private Id $id;

    /**
     * @var Resource
     */
    private Annotation\Resource $resource;

    /**
     * @var Attribute[]|ArrayCollection
     */
    private ArrayCollection $attributes;

    /**
     * @var Relationship[]|ArrayCollection
     */
    private ArrayCollection $relationships;

    /**
     * ClassMetadata constructor.
     *
     * @param string              $className
     * @param Id                  $id
     * @param Annotation\Resource $resource
     * @param ArrayCollection     $attributes
     * @param ArrayCollection     $relationships
     */
    public function __construct(
        string $className,
        Id $id,
        Annotation\Resource $resource,
        ArrayCollection $attributes,
        ArrayCollection $relationships
    ) {
        $this->className = $className;
        $this->id = $id;
        $this->resource = $resource;
        $this->attributes = $attributes;
        $this->relationships = $relationships;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return Id
     */
    public function getId(): Id
    {
        return $this->id;
    }

    /**
     * @return Annotation\Resource
     */
    public function getResource(): Annotation\Resource
    {
        return $this->resource;
    }

    /**
     * @return Attribute[]|ArrayCollection
     */
    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     *
     * @return Attribute
     * @throws AttributeNotFound
     */
    public function getAttribute(string $name): Attribute
    {
        if ($this->attributes->containsKey($name)) {
            return $this->attributes->get($name);
        }
        throw new AttributeNotFound($name, $this->getResource()->type);
    }

    /**
     * @return Relationship[]|ArrayCollection
     */
    public function getRelationships(): ArrayCollection
    {
        return $this->relationships;
    }

    /**
     * @param string $name
     *
     * @return Relationship
     * @throws RelationNotFound
     */
    public function getRelationship(string $name): Relationship
    {
        if ($this->relationships->containsKey($name)) {
            return $this->relationships->get($name);
        }
        throw new RelationNotFound($name, $this->getResource()->type);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function isRelationship(string $fieldName): bool
    {
        return $this->relationships->containsKey($fieldName);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function isAttribute(string $fieldName): bool
    {
        return $this->attributes->containsKey($fieldName);
    }
}
