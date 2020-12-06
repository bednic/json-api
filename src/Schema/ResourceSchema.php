<?php

declare(strict_types=1);

namespace JSONAPI\Schema;

use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\Id;
use JSONAPI\Metadata\Meta;
use JSONAPI\Metadata\Relationship;

/**
 * Class ResourceSchema
 *
 * @package JSONAPI\Schema
 */
final class ResourceSchema
{
    /**
     * @var string
     */
    private string $className;
    /**
     * @var string
     */
    private string $type;
    /**
     * @var Id
     */
    private Id $id;
    /**
     * @var Attribute[]
     */
    private array $attributes;
    /**
     * @var Relationship[]
     */
    private array $relationships;
    /**
     * @var bool
     */
    private bool $readOnly;
    /**
     * @var Meta|null
     */
    private ?Meta $meta;

    /**
     * ResourceSchema constructor.
     *
     * @param string         $className
     * @param string         $type
     * @param Id             $id
     * @param Attribute[]    $attributes
     * @param Relationship[] $relationships
     * @param bool           $readOnly
     * @param Meta|null      $resourceMeta
     */
    public function __construct(
        string $className,
        string $type,
        Id $id,
        array $attributes = [],
        array $relationships = [],
        bool $readOnly = false,
        ?Meta $resourceMeta = null
    ) {
        $this->className     = $className;
        $this->type          = $type;
        $this->id            = $id;
        $this->attributes    = $attributes;
        $this->relationships = $relationships;
        $this->readOnly      = $readOnly;
        $this->meta          = $resourceMeta;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Id
     */
    public function getId(): Id
    {
        return $this->id;
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
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * @return Meta|null
     */
    public function getMeta(): ?Meta
    {
        return $this->meta;
    }
}
