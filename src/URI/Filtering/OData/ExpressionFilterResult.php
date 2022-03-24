<?php

namespace JSONAPI\URI\Filtering\OData;

use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\FilterInterface;

class ExpressionFilterResult implements FilterInterface
{

    public function __construct(
        private string $origin,
        private mixed $condition,
        private ?Collection $joins = null,
    )
    {
    }

    public function getCondition(): mixed
    {
        // TODO: Implement getCondition() method.
    }

    public function getRequiredJoins(): Collection
    {
        // TODO: Implement getRequiredJoins() method.
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }
}
