<?php

declare(strict_types=1);

namespace JSONAPI\URI\Inclusion;

/**
 * Class Inclusion
 *
 * @package JSONAPI\URI\Inclusion
 */
class Inclusion
{
    /**
     * @var string ResourceMetadata type
     */
    private string $relationName;

    /**
     * @var Inclusion[]
     */
    private array $inclusions = [];

    /**
     * Inclusion constructor.
     *
     * @param string $relationName
     */
    public function __construct(string $relationName)
    {
        $this->relationName = $relationName;
    }

    /**
     * @return bool
     */
    public function hasInclusions(): bool
    {
        return count($this->inclusions) > 0;
    }

    /**
     * @param Inclusion $inclusion
     */
    public function addInclusion(Inclusion $inclusion)
    {
        $this->inclusions[$inclusion->getRelationName()] = $inclusion;
    }

    /**
     * @return string
     */
    public function getRelationName(): string
    {
        return $this->relationName;
    }

    /**
     * @return Inclusion[]
     */
    public function getInclusions(): array
    {
        return $this->inclusions;
    }

    /**
     * @param string $relation
     *
     * @return bool
     */
    public function inclusionContains(string $relation): bool
    {
        return array_key_exists($relation, $this->inclusions);
    }
}
