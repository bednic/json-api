<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\QData;

use ExpressionBuilder\Expression;
use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\FilterInterface;

class QuatrodotResult implements FilterInterface
{
    /**
     * @param string|null                         $origin
     * @param Expression|null                     $condition
     * @param Collection<string, Expression>|null $identifierExpressions
     */
    public function __construct(
        private ?string $origin = null,
        private ?Expression $condition = null,
        private ?Collection $identifierExpressions = null
    ) {
    }


    /**
     * @return Expression
     */
    public function getCondition(): Expression
    {
        return $this->condition;
    }

    /**
     * @param string $identifier
     *
     * @return Expression|null
     */
    public function getPartialCondition(string $identifier): ?Expression
    {
        if ($this->identifierExpressions?->hasKey($identifier)) {
            return $this->identifierExpressions->get($identifier);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->origin ?? '';
    }
}
