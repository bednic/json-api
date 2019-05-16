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
     * @var string|int|null
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
     * @param string          $resource
     * @param int|string|null $id
     * @param string|null     $relationship
     * @param string|null     $relation
     * @param string|null     $query
     */
    public function __construct(
        string $resource,
        $id = null,
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
     * @return int|string|null
     */
    public function getId()
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->resource
            . ($this->id ? '/' . $this->getId() : '')
            . ($this->relationship ? '/relationship/' . $this->getRelationshipName() : '')
            . ($this->relation ? '/' . $this->getRelationshipName() : '')
            . ($this->query ? $this->query : '');
    }
}
