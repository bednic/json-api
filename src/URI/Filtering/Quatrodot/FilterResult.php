<?php

/**
 * Created by uzivatel
 * at 22.03.2022 15:27
 */

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Quatrodot;

use ExpressionBuilder\Ex;
use ExpressionBuilder\Expression;
use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\FilterInterface;

/**
 * Class FilterResult
 *
 * @package JSONAPI\URI\Filtering\Quatrodot
 */
class FilterResult implements FilterInterface
{
    private Collection $expressions;

    public function __construct()
    {
        $this->expressions = new Collection();
    }

    public function addFieldExpression(string $field, Expression $expression): void
    {
        if ($this->expressions->hasKey($field)) {
            $old = $this->expressions->get($field);
            $new = Ex::or($old, $expression);
            $this->expressions->set($field, $new);
        } else {
            $this->expressions->set($field, $expression);
        }
    }

    public function getCondition(): Collection
    {
        return $this->expressions;
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }
}
