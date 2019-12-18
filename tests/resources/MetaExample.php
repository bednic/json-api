<?php

namespace JSONAPI\Test;

use JSONAPI\Document\Meta;
use JSONAPI\Annotation as API;

/**
 * Class MetaExample
 *
 * @package JSONAPI\Test
 * @API\Resource(type="meta", meta=@API\Meta(getter="getMeta"))
 */
class MetaExample
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
     * @API\Id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Meta
     */
    public function getMeta(): Meta
    {
        return new Meta([
            'for' => MetaExample::class
        ]);
    }

    /**
     * @return DummyRelation
     * @API\Relationship(target=DummyRelation::class, meta=@API\Meta(getter="getRelationMeta"))
     */
    public function getRelation(): DummyRelation
    {
        return new DummyRelation('relation1');
    }

    /**
     * @return Meta
     */
    public function getRelationMeta(): Meta
    {
        return new Meta([
            'for' => DummyRelation::class
        ]);
    }
}
