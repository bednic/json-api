<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\QData;

use ExpressionBuilder\Expression;
use JSONAPI\URI\Filtering\FilterInterface;

/**
 * Class QuatrodotResult
 *
 * @package JSONAPI\URI\Filtering\QData
 */
class QuatrodotResult implements FilterInterface
{
    /**
     * @var Expression|null
     */
    private ?Expression $condition = null;

    /**
     * @var array<string, array<int, Expression\Type\TBoolean>>
     */
    private array $tree = [];

    /**
     * @var string|null
     */
    private ?string $origin = null;

    /**
     * @param Expression $condition
     */
    public function setCondition(Expression $condition): void
    {
        $this->condition = $condition;
    }

    /**
     * @param string $origin
     */
    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    /**
     * @return Expression|null
     */
    public function getCondition(): ?Expression
    {
        return $this->condition;
    }

    /**
     * @param string $identifier
     *
     * @return Expression\Type\TBoolean[]
     */
    public function getConditionsFor(string $identifier): array
    {
        if (isset($this->tree[$identifier])) {
            return $this->tree[$identifier];
        }
        return [];
    }

    public function addConditionFor(string $identifier, Expression\Type\TBoolean $expression): void
    {
        $this->tree[$identifier][] = $expression;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->origin ? 'filter=' . urlencode($this->origin) : '';
    }
}
