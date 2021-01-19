<?php

/**
 * Created by IntelliJ IDEA.
 * User: tomas
 * Date: 24.04.2019
 * Time: 12:47
 */

declare(strict_types=1);

namespace JSONAPI\Test\Resources\Valid;

use Doctrine\Common\Collections\ArrayCollection;
use JSONAPI\Annotation as API;
use JSONAPI\Data\Collection;
use JSONAPI\Helper\DoctrineCollectionAdapter;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\Id;
use JSONAPI\Metadata\Relationship;
use JSONAPI\Schema\Resource;
use JSONAPI\Schema\ResourceSchema;

/**
 * Class GettersExample
 *
 * @package JSONAPI
 */
#[API\Resource("getter")]
class GettersExample implements Resource
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
     * @var int[]
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
     * @var Collection<DummyRelation>
     */
    private Collection $collection;

    /**
     * PropsExample constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id          = $id;
        $this->relation    = new DummyRelation('relation1');
        $this->collection  = new Collection(
            [
                new DummyRelation('relation2'),
                new DummyRelation('relation3')
            ]
        );
        $this->dtoProperty = new DtoValue();
    }

    /**
     * @return string
     */
    #[API\Id]
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
     * @return string
     */
    #[API\Attribute]
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
     * @return int
     */
    #[API\Attribute]
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
     * Return array property value
     *
     * @return int[]
     */
    #[API\Attribute]
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
     * @return bool
     */
    #[API\Attribute]
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
     * @return DtoValue
     */
    #[API\Attribute]
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
     * @return DummyRelation
     */
    #[API\Relationship(DummyRelation::class)]
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
     * @return Collection<DummyRelation>
     */
    #[API\Relationship(DummyRelation::class)]
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * @param Collection<DummyRelation> $collection
     */
    public function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * @return DoctrineCollectionAdapter
     */
    #[API\Relationship(DummyRelation::class)]
    public function getDoctrineCollection(): DoctrineCollectionAdapter
    {
        return new DoctrineCollectionAdapter(new ArrayCollection([]));
    }

    public static function getSchema(): ResourceSchema
    {
        return new ResourceSchema(
            __CLASS__,
            'getter',
            Id::createByMethod('getId'),
            [
                Attribute::createByMethod('getStringProperty'),
                Attribute::createByMethod('getIntProperty'),
                Attribute::createByMethod('getArrayProperty', 'int'),
                Attribute::createByMethod('isBoolProperty'),
                Attribute::createByMethod('getDtoProperty'),
            ],
            [
                Relationship::createByMethod('getRelation', DummyRelation::class),
                Relationship::createByMethod('getCollection', DummyRelation::class)
            ]
        );
    }
}
