<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 13:03
 */

namespace JSONAPI;

use JSONAPI\Annotation;

/**
 * Class ClassMetadata
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
     * @param Annotation\Id       $id
     * @param Annotation\Resource $resource
     * @param array    $attributes
     * @param array    $relationships
     */
    public function __construct(Annotation\Id $id, Annotation\Resource $resource, array $attributes, array $relationships)
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
     * @return Annotation\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return Annotation\Attribute|null
     */
    public function getAttribute(string $name): ?Annotation\Attribute
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->name == $name) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * @return Annotation\Relationship[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * @param string $name
     * @return Annotation\Relationship|null
     */
    public function getRelationship(string $name): ?Annotation\Relationship
    {
        foreach ($this->relationships as $relationship) {
            if ($relationship->name == $name) {
                return $relationship;
            }
        }
        return null;
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function isRelationship(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->relationships);
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function isAttribute(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->attributes);
    }
}
