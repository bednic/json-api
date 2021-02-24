<?php

declare(strict_types=1);

namespace JSONAPI\Test\Resources\Valid;

use JSONAPI\Annotation as API;
use JSONAPI\Document\Meta;
use JSONAPI\Exception\Document\ForbiddenCharacter;
use JSONAPI\Exception\Document\ForbiddenDataType;
use JSONAPI\Metadata\Id;
use JSONAPI\Metadata\Relationship;
use JSONAPI\Schema\Resource;
use JSONAPI\Schema\ResourceSchema;

/**
 * Class MetaExample
 *
 * @package JSONAPI\Test
 */
#[API\Resource("meta")]
#[API\Meta("getMeta")]
class MetaExample implements Resource
{
    /**
     * @var string
     */
    private string $id;

    /**
     * MetaExample constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
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
     * @return Meta
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function getMeta(): Meta
    {
        return new Meta(
            [
                'for' => MetaExample::class
            ]
        );
    }

    /**
     * @return DummyRelation
     */
    #[API\Relationship(DummyRelation::class)]
    #[API\Meta("getRelationMeta")]
    public function getRelation(): DummyRelation
    {
        return new DummyRelation('relation1');
    }

    /**
     * @return Meta
     * @throws ForbiddenCharacter
     * @throws ForbiddenDataType
     */
    public function getRelationMeta(): Meta
    {
        return new Meta(
            [
                'for' => DummyRelation::class
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public static function getSchema(): ResourceSchema
    {
        return new ResourceSchema(
            __CLASS__,
            'meta',
            Id::createByMethod('getId'),
            [],
            [
                Relationship::createByMethod(
                    'getRelation',
                    DummyRelation::class,
                    null,
                    null,
                    false,
                    \JSONAPI\Metadata\Meta::create('getRelationMeta')
                )
            ],
            false,
            \JSONAPI\Metadata\Meta::create('getMeta')
        );
    }
}
