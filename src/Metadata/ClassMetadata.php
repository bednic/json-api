<?php

declare(strict_types=1);

namespace JSONAPI\Metadata;

use JSONAPI\Data\Collection;
use JSONAPI\Exception\Metadata\AlreadyInUse;
use JSONAPI\Metadata;
use JSONAPI\Exception\Metadata\AttributeNotFound;
use JSONAPI\Exception\Metadata\RelationNotFound;

/**
 * Class ClassMetadata
 *
 * @package JSONAPI
 */
final class ClassMetadata
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
     * @var bool
     */
    private bool $readOnly;

    /**
     * @var Metadata\Id
     */
    private Metadata\Id $id;

    /**
     * @var Collection|Field[]
     */
    private Collection $fields;
    /**
     * @var Meta|null
     */
    private ?Meta $meta;

    /**
     * ClassMetadata constructor.
     *
     * @param string         $className
     * @param string         $type
     * @param Metadata\Id    $id
     * @param Attribute[]    $attributes
     * @param Relationship[] $relationships
     * @param bool           $readOnly
     * @param Meta|null      $resourceMeta
     *
     * @throws AlreadyInUse
     */
    public function __construct(
        string $className,
        string $type,
        Metadata\Id $id,
        iterable $attributes,
        iterable $relationships,
        bool $readOnly = false,
        ?Meta $resourceMeta = null
    ) {
        $this->fields = new Collection();
        $this->className = $className;
        $this->id = $id;
        $this->type = $type;
        $this->readOnly = $readOnly;
        $this->meta = $resourceMeta;
        $this->fields->set('id', $id);
        $this->fields->set('type', $type);
        foreach ($attributes as $attribute) {
            if ($this->fields->hasKey($attribute->name)) {
                throw new AlreadyInUse($attribute->name);
            }
            $this->fields->set($attribute->name, $attribute);
        }
        foreach ($relationships as $relationship) {
            if ($this->fields->hasKey($relationship->name)) {
                throw new AlreadyInUse($relationship->name);
            }
            $this->fields->set($relationship->name, $relationship);
        }
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return Id
     */
    public function getId(): Id
    {
        return $this->id;
    }


    public function getType(): string
    {
        return $this->type;
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

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->fields->filter(fn($i) => $i instanceof Attribute)->toArray();
    }

    /**
     * @param string $name
     *
     * @return Attribute
     * @throws AttributeNotFound
     */
    public function getAttribute(string $name): Attribute
    {
        if ($this->hasAttribute($name)) {
            return $this->fields->get($name);
        }
        throw new AttributeNotFound($name, $this->type);
    }

    /**
     * @return Relationship[]
     */
    public function getRelationships(): array
    {
        return $this->fields->filter(fn($i) => $i instanceof Relationship)->toArray();
    }

    /**
     * @param string $name
     *
     * @return Relationship
     * @throws RelationNotFound
     */
    public function getRelationship(string $name): Relationship
    {
        if ($this->hasRelationship($name)) {
            return $this->fields->get($name);
        }
        throw new RelationNotFound($name, $this->getType());
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasRelationship(string $fieldName): bool
    {
        return $this->fields->filter(fn($i) => $i instanceof Relationship)->hasKey($fieldName);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasAttribute(string $fieldName): bool
    {
        return $this->fields->filter(fn($i) => $i instanceof Attribute)->hasKey($fieldName);
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasField(string $fieldName): bool
    {
        return $this->fields->hasKey($fieldName);
    }
}
