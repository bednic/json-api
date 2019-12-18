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
 * Class GettersExample
 *
 * @package JSONAPI
 * @API\Resource("getter")
 */
class GettersExample
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var string
     */
    private string $stringProperty = 'string value';
    /**
     * @var int
     */
    private int $intProperty = 1;

    /**
     * @var array
     */
    private array $arrayProperty = [1, 2, 3];

    /**
     * @var bool
     */
    public bool $boolProperty = true;

    /**
     * @var DtoValue
     */
    private DtoValue $dtoProperty;

    /**
     * @var DummyRelation
     */
    private DummyRelation $relation;

    /**
     * @var Collection|DummyRelation[]
     */
    private Collection $collection;

    /**
     * PropsExample constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->relation = new DummyRelation('relation1');
        $this->collection = new ArrayCollection([
            new DummyRelation('relation2'),
            new DummyRelation('relation3')
        ]);
        $this->dtoProperty = new DtoValue();
    }

    /**
     * @API\Id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @API\Attribute
     * @return string
     */
    public function getStringProperty(): string
    {
        return $this->stringProperty;
    }

    /**
     * @param string $stringProperty
     */
    public function setStringProperty(string $stringProperty): void
    {
        $this->stringProperty = $stringProperty;
    }

    /**
     * @API\Attribute
     * @return int
     */
    public function getIntProperty(): int
    {
        return $this->intProperty;
    }

    /**
     * @param int $intProperty
     */
    public function setIntProperty(int $intProperty): void
    {
        $this->intProperty = $intProperty;
    }

    /**
     * @API\Attribute
     * @return array
     */
    public function getArrayProperty(): array
    {
        return $this->arrayProperty;
    }

    /**
     * @param array $arrayProperty
     */
    public function setArrayProperty(array $arrayProperty): void
    {
        $this->arrayProperty = $arrayProperty;
    }

    /**
     * @API\Attribute
     * @return bool
     */
    public function isBoolProperty(): bool
    {
        return $this->boolProperty;
    }

    /**
     * @param bool $boolProperty
     */
    public function setBoolProperty(bool $boolProperty): void
    {
        $this->boolProperty = $boolProperty;
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
     * @API\Relationship(target=DummyRelation::class)
     * @return DummyRelation
     */
    public function getRelation(): DummyRelation
    {
        return $this->relation;
    }

    /**
     * @param DummyRelation $relation
     */
    public function setRelation(DummyRelation $relation): void
    {
        $this->relation = $relation;
    }

    /**
     * @API\Relationship(target=DummyRelation::class)
     * @return Collection|DummyRelation[]
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * @param Collection|DummyRelation[] $collection
     */
    public function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
    }
}
