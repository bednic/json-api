<?php

declare(strict_types=1);

namespace JSONAPI\Test\Resources\Valid;

use JSONAPI\Annotation as API;
use JSONAPI\Data\Collection;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\Id;
use JSONAPI\Metadata\Relationship;
use JSONAPI\Schema\Resource;
use JSONAPI\Schema\ResourceSchema;

/**
 * Class PropsExample
 *
 * @package JSONAPI\Test
 * @API\Resource(type="prop")
 */
#[API\Resource("prop")]
class PropsExample implements Resource
{
    /**
     * @var string
     */
    #[API\Id]
    public string $id;

    /**
     * @var string
     */
    #[API\Attribute]
    public string $stringProperty = 'string value';
    /**
     * @var int
     */
    #[API\Attribute]
    public int $intProperty = 1;

    /**
     * @var int[]
     */
    #[API\Attribute(of: 'int')]
    public array $arrayProperty = [1, 2, 3];

    /**
     * @var bool
     */
    #[API\Attribute]
    public bool $boolProperty = true;

    /**
     * @var DtoValue
     */
    #[API\Attribute]
    public DtoValue $dtoProperty;

    /**
     * @var DummyRelation
     */
    #[API\Relationship(DummyRelation::class)]
    public DummyRelation $relation;

    /**
     * @var Collection<DummyRelation>
     */
    #[API\Relationship(DummyRelation::class)]
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
        $this->collection = new Collection([
            new DummyRelation('relation2'),
            new DummyRelation('relation3')
        ]);
        $this->dtoProperty = new DtoValue();
    }

    public static function getSchema(): ResourceSchema
    {
        return new ResourceSchema(
            __CLASS__,
            'prop',
            Id::createByProperty('getId'),
            [
                Attribute::createByProperty('stringProperty'),
                Attribute::createByProperty('intProperty'),
                Attribute::createByProperty('arrayProperty','int'),
                Attribute::createByProperty('boolProperty'),
                Attribute::createByProperty('dtoProperty'),
            ],
            [
                Relationship::createByProperty('relation',DummyRelation::class),
                Relationship::createByProperty('collection', DummyRelation::class)
            ]
        );
    }
}
