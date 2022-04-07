<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\OData;

use ExpressionBuilder\Expression;
use JSONAPI\URI\Filtering\FilterInterface;

/**
 * Class ExpressionFilterResult
 *
 * @package JSONAPI\URI\Filtering\OData
 */
class ExpressionFilterResult implements FilterInterface
{
    /**
     * @param string|null     $origin
     * @param Expression|null $condition
     */
    public function __construct(
        private ?string $origin = null,
        private ?Expression $condition = null
    ) {
    }

    /**
     * @return Expression|null
     */
    public function getCondition(): ?Expression
    {
        return $this->condition;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->origin ? 'filter=' . $this->origin : '';
    }
}
