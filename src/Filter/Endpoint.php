<?php


namespace JSONAPI\Filter;

/**
 * Class Endpoint
 *
 * @package JSONAPI\Filter
 */
class Endpoint
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
     * Endpoint constructor.
     *
     * @param string          $resource
     * @param int|string|null $id
     * @param string|null     $relationship
     * @param string|null     $relation
     */
    public function __construct(string $resource, $id = null, ?string $relationship = null, ?string $relation = null)
    {
        $this->resource = $resource;
        $this->id = $id;
        $this->relationship = $relationship;
        $this->relation = $relation;
    }

    /**
     * @return string
     */
    public function getResourceType(): string
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
    public function getRelationshipType(): ?string
    {
        return $this->relationship;
    }

    /**
     * @return string|null
     */
    public function getRelationType(): ?string
    {
        return $this->relation;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return empty($this->id);
    }

    /**
     * @return string
     */
    public function getPrimaryDataType(): string
    {
        if ($this->relation) {
            return $this->getRelationType();
        } elseif ($this->relationship) {
            return $this->getRelationshipType();
        } else {
            return $this->getResourceType();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '/' . $this->resource
            . ($this->getId() ? '/' . $this->getId() : '')
            . ($this->getRelationshipType() ? '/' . $this->getRelationshipType() : '')
            . ($this->getRelationType() ? '/' . $this->getRelationType() : '');
    }
}
