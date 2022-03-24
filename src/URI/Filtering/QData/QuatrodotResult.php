<?php

namespace JSONAPI\URI\Filtering\QData;

use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\FilterInterface;

class QuatrodotResult implements FilterInterface
{
    /**
     * @param string|null     $origin
     * @param mixed           $condition
     * @param Collection|null $joins
     * @param Collection|null $identifierExpressions
     */
    public function __construct(
        private ?string $origin = null,
        private mixed $condition = null,
        private ?Collection $joins = null,
        private ?Collection $identifierExpressions = null
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getCondition(): mixed
    {
        return $this->condition;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredJoins(): Collection
    {
        return $this->joins ?? new Collection();
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function getPartialCondition(string $identifier): mixed
    {
        return $this->identifierExpressions?->get($identifier);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->origin;
    }
}
