<?php

namespace JSONAPI\Test\Resources\Valid;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JSONAPI\Metadata as API;

/**
 * Class PropsExample
 *
 * @package JSONAPI\Test
 * @API\Resource(type="prop")
 */
class PropsExample
{
    /**
     * @var string
     * @API\Id
     */
    public string $id;

    /**
     * @var string
     * @API\Attribute
     */
    public string $stringProperty = 'string value';
    /**
     * @var int
     * @API\Attribute
     */
    public int $intProperty = 1;

    /**
     * @var array
     * @API\Attribute(of="int")
     */
    public array $arrayProperty = [1, 2, 3];

    /**
     * @var bool
     * @API\Attribute
     */
    public bool $boolProperty = true;

    /**
     * @var DtoValue
     * @API\Attribute
     */
    public DtoValue $dtoProperty;

    /**
     * @var DummyRelation
     * @API\Relationship(target=DummyRelation::class)
     */
    public DummyRelation $relation;

    /**
     * @var Collection|DummyRelation[]
     * @API\Relationship(target=DummyRelation::class)
     */
    public Collection $collection;

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
}
