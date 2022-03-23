<?php

/**
 * Created by uzivatel
 * at 22.03.2022 16:30
 */

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use ExpressionBuilder\Ex;
use ExpressionBuilder\Expression;
use ExpressionBuilder\Expression\Field;
use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\ExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\Messages;

/**
 * Class FieldExpressionBuilder
 *
 * @package JSONAPI\URI\Filtering\Quatrodot
 */
class FieldExpressionBuilder implements ExpressionBuilder
{
    /**
     * @var Collection fields
     */
    private Collection $fields;

    public function __construct()
    {
        $this->fields = new Collection();
    }

    public function and(mixed $left, mixed $right): mixed
    {
        return $this->fields;
    }

    public function or(mixed $left, mixed $right): mixed
    {
        return Ex::or($left, $right);
    }

    public function eq(mixed $left, mixed $right): mixed
    {
        $ex = Ex::eq($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    /**
     * @param Field      $field
     * @param Expression $expression
     *
     * @return void
     */
    private function addFieldExpression(Field $field, Expression $expression): void
    {
        $identifier = $field->getName();
        if ($this->fields->hasKey($identifier)) {
            $old = $this->fields->get($identifier);
            $new = Ex::or($old, $expression);
            $this->fields->set($identifier, $new);
        } else {
            $this->fields->set($identifier, $expression);
        }
    }

    public function ne(mixed $left, mixed $right): mixed
    {
        $ex = Ex::ne($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function gt(mixed $left, mixed $right): mixed
    {
        $ex = Ex::gt($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function ge(mixed $left, mixed $right): mixed
    {
        $ex = Ex::ge($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function lt(mixed $left, mixed $right): mixed
    {
        $ex = Ex::lt($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function le(mixed $left, mixed $right): mixed
    {
        $ex = Ex::le($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function in(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function has(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function add(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function sub(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function mul(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function div(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function mod(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function not(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function toupper(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function tolower(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function trim(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function length(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function concat(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function contains(mixed $column, mixed $args): mixed
    {
        $ex = Ex::contains($column, $args);
        $this->addFieldExpression($column, $ex);
        return $ex;
    }

    public function startsWith(mixed $column, mixed $args): mixed
    {
        $ex = Ex::contains($column, $args);
        $this->addFieldExpression($column, $ex);
        return $ex;
    }

    public function endsWith(mixed $column, mixed $args): mixed
    {
        $ex = Ex::contains($column, $args);
        $this->addFieldExpression($column, $ex);
        return $ex;
    }

    public function substring(mixed $column, mixed $start, mixed $end = null): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function indexOf(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function pattern(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function ceil(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function floor(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function round(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function isNull(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function isNotNull(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function literal(mixed $value): mixed
    {
        return Ex::literal($value);
    }

    public function date(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function day(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function hour(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function minute(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function month(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function now(): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function second(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function time(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function year(mixed $column): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(__FUNCTION__));
    }

    public function parseIdentifier(string $identifier): mixed
    {
        return Ex::field($identifier);
    }
}
