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

/**
 * Class ClassMetadata
 *
 * @package JSONAPI
 */
final class ClassMetadata
{

    /**
     * @var Annotation\Id
     */
    private $id;
    /**
     * @var Resource
     */
    private $resource;
    /**
     * @var Annotation\Attribute[]
     */
    private $attributes;
    /**
     * @var Annotation\Relationship[]
     */
    private $relationships;

    /**
     * ClassMetadata constructor.
     *
     * @param Annotation\Id       $id
     * @param Annotation\Resource $resource
     * @param ArrayCollection     $attributes
     * @param ArrayCollection     $relationships
     */
    public function __construct(Annotation\Id $id, Annotation\Resource $resource, ArrayCollection $attributes, ArrayCollection $relationships)
    {
        $this->id = $id;
        $this->resource = $resource;
        $this->attributes = $attributes;
        $this->relationships = $relationships;
    }

    /**
     * @return Annotation\Id
     */
    public function getId(): Annotation\Id
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
     * @return Annotation\Attribute[]|ArrayCollection
     */
    public function getAttributes(): ArrayCollection
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return Annotation\Attribute|null
     */
    public function getAttribute(string $name): ?Annotation\Attribute
    {
        return $this->attributes->get($name);
    }

    /**
     * @return Annotation\Relationship[]|ArrayCollection
     */
    public function getRelationships(): ArrayCollection
    {
        return $this->relationships;
    }

    /**
     * @param string $name
     * @return Annotation\Relationship|null
     */
    public function getRelationship(string $name): ?Annotation\Relationship
    {
        return $this->relationships->get($name);
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function isRelationship(string $fieldName): bool
    {
        return $this->relationships->containsKey($fieldName);
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function isAttribute(string $fieldName): bool
    {
        return $this->attributes->containsKey($fieldName);
    }
}
