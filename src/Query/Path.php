<?php

namespace JSONAPI\Query;

/**
 * Class Path
 *
 * @package JSONAPI\Query
 */
class Path
{
    /**
     * @var string
     */
    private $resource;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $relationship;

    /**
     * @var string|null
     */
    private $relation;

    /**
     * @var string
     */
    private $query;

    /**
     * Path constructor.
     *
     * @param string      $resource
     * @param string|null $id
     * @param string|null $relationship
     * @param string|null $relation
     * @param string|null $query
     */
    public function __construct(
        string $resource,
        string $id = null,
        string $relationship = null,
        string $relation = null,
        string $query = null
    ) {
        $this->resource = $resource;
        $this->id = $id;
        $this->relationship = $relationship;
        $this->relation = $relation;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @return bool
     */
    public function isRelation(): bool
    {
        return $this->isRelationship() || !empty($this->relation);
    }

    /**
     * @return bool
     */
    public function isRelationship(): bool
    {
        return !empty($this->relationship);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->resource
            . ($this->id ? '/' . $this->getId() : '')
            . ($this->relationship ? '/relationships/' . $this->getRelationshipName() : '')
            . ($this->relation ? '/' . $this->getRelationshipName() : '')
            . ($this->query ? '?' . $this->query : '');
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getRelationshipName(): ?string
    {
        if ($this->relation) {
            return $this->relation;
        } elseif ($this->relationship) {
            return $this->relationship;
        } else {
            return null;
        }
    }
}
