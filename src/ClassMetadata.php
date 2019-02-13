<?php
/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 04.02.2019
 * Time: 13:03
 */

namespace OpenAPI;


use OpenAPI\Annotation\Attribute;
use OpenAPI\Annotation\Id;
use OpenAPI\Annotation\Relationship;
use OpenAPI\Annotation\Resource;

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
     * @param Id $id
     * @param Resource $resource
     * @param array $attributes
     * @param array $relationships
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

    public function getRelationship(string $name): ?Relationship
    {
        foreach ($this->relationships as $relationship){
            if($relationship->name == $name){
                return $relationship;
            }
        }
        return null;
    }
}
