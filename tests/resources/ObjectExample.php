<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:47
 */

namespace JSONAPI\Test;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Test\resources\DtoValue;
use JSONAPI\Annotation as API;

/**
 * Class ObjectExample
 *
 * @package JSONAPI
 * @API\Resource("resource")
 */
class ObjectExample extends Common
{
    /**
     * @var string
     */
    protected $id = 'uuid';

    /**
     * @API\Attribute
     * @var string
     */
    public $publicProperty = 'public-value';

    /**
     * @var string
     */
    private $privateProperty = 'private-value';

    /**
     * @var string
     */
    private $readOnlyProperty = 'read-only-value';

    /**
     * @var DtoValue
     */
    private $dtoProperty;

    /**
     * @var RelationExample[]|Collection
     */
    private $relations;

    /**
     * @var ObjectExample
     */
    private $parent;

    /**
     * @var ObjectExample[]|Collection
     */
    private $children;

    /**
     * ObjectExample constructor.
     *
     * @param string|null $id
     */
    public function __construct(string $id = null)
    {
        parent::__construct($id);
        $this->dtoProperty = new DtoValue();
        $this->children = new ArrayCollection();
        $this->relations = new ArrayCollection();
    }


    /**
     * @API\Attribute
     * @return string
     */
    public function getPrivateProperty(): string
    {
        return $this->privateProperty;
    }

    /**
     * @param string $privateProperty
     */
    public function setPrivateProperty(string $privateProperty): void
    {
        $this->privateProperty = $privateProperty;
    }

    /**
     * @API\Attribute(setter=false)
     * @return string
     */
    public function getReadOnlyProperty(): string
    {
        return $this->readOnlyProperty;
    }

    /**
     * @API\Attribute
     * @return DtoValue
     */
    public function getDtoProperty(): DtoValue
    {
        return $this->dtoProperty;
    }

    /**
     * @param DtoValue $dtoProperty
     */
    public function setDtoProperty(DtoValue $dtoProperty): void
    {
        $this->dtoProperty = $dtoProperty;
    }

    /**
     * @API\Relationship(target=RelationExample::class)
     * @return RelationExample[]|Collection
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    /**
     * @param RelationExample[]|Collection $relations
     */
    public function setRelations(Collection $relations): void
    {
        foreach ($relations as $relation) {
            $relation->setObject($this);
        }
        $this->relations = $relations;
    }

    /**
     * @API\Relationship(target=ObjectExample::class)
     * @return ObjectExample
     */
    public function getParent(): ObjectExample
    {
        return $this->parent;
    }

    /**
     * @param ObjectExample $parent
     */
    public function setParent(ObjectExample $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @API\Relationship(target=ObjectExample::class)
     * @return ObjectExample[]|Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * @param ObjectExample[]|Collection $children
     */
    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    /**
     * @API\Meta
     * @return bool
     */
    public function getState(): bool
    {
        return true;
    }
}
