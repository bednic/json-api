<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 13:03
 */

namespace JSONAPI;


use JSONAPI\Annotation\Attribute;
use JSONAPI\Annotation\Id;
use JSONAPI\Annotation\Relationship;
use JSONAPI\Annotation\Resource;

/**
 * Class ClassMetadata
 * @package JSONAPI
 */
class ClassMetadata
{

    /**
     * @var Id
     */
    private $id;
    /**
     * @var Resource
     */
    private $resource;
    /**
     * @var Attribute[]
     */
    private $attributes;
    /**
     * @var Relationship[]
     */
    private $relationships;

    /**
     * ClassMetadata constructor.
     * @param Id       $id
     * @param Resource $resource
     * @param array    $attributes
     * @param array    $relationships
     */
    public function __construct(Id $id, Resource $resource, array $attributes, array $relationships)
    {
        $this->id = $id;
        $this->resource = $resource;
        $this->attributes = $attributes;
        $this->relationships = $relationships;
    }

    /**
     * @return Id
     */
    public function getId(): Id
    {
        return $this->id;
    }

    /**
     * @return Resource
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return Relationship[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * @param string $name
     * @return Relationship|null
     */
    public function getRelationship(string $name): ?Relationship
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
