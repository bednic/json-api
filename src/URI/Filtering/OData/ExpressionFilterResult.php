<?php

namespace JSONAPI\URI\Filtering\OData;

use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\FilterInterface;

/**
 * Class ExpressionFilterResult
 *
 * @package JSONAPI\URI\Filtering\OData
 */
class ExpressionFilterResult implements FilterInterface
{
    /**
     * @param string $origin
     * @param mixed  $condition
     */
    public function __construct(
        private string $origin,
        private mixed $condition
    ) {
    }

    /**
     * @return mixed
     */
    public function getCondition(): mixed
    {
        return $this->condition;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->origin;
    }
}
