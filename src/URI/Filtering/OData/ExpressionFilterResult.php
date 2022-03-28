<?php

declare(strict_types=1);

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
     * @param string|null $origin
     * @param mixed       $condition
     */
    public function __construct(
        private ?string $origin = null,
        private mixed $condition = null
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
        return $this->origin ?? '';
    }
}
