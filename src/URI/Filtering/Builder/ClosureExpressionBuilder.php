<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use ExpressionBuilder\Ex;
use ExpressionBuilder\Expression\Field;
use ExpressionBuilder\Expression\Literal;
use ExpressionBuilder\Expression\TBoolean;
use ExpressionBuilder\Expression\TNumeric;
use ExpressionBuilder\Expression\TString;
use JSONAPI\URI\Filtering\Constants;
use JSONAPI\URI\Filtering\ExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\Messages;

class ClosureExpressionBuilder implements ExpressionBuilder, UseDottedIdentifier
{
    /**
     * @inheritDoc
     */
    public function and(mixed $left, mixed $right): TBoolean
    {
        return Ex::and($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function or(mixed $left, mixed $right): TBoolean
    {
        return Ex::or($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function eq(mixed $left, mixed $right): TBoolean
    {
        return Ex::eq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ne(mixed $left, mixed $right): TBoolean
    {
        return Ex::ne($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function gt(mixed $left, mixed $right): TBoolean
    {
        return Ex::gt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ge(mixed $left, mixed $right): TBoolean
    {
        return Ex::ge($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function lt(mixed $left, mixed $right): TBoolean
    {
        return Ex::lt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function le(mixed $left, mixed $right): TBoolean
    {
        return Ex::le($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function in(mixed $column, mixed $args): TBoolean
    {
        return Ex::in($column, new Literal($args));
    }

    /**
     * @inheritDoc
     */
    public function add(mixed $left, mixed $right): TNumeric
    {
        return Ex::add($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function sub(mixed $left, mixed $right): TNumeric
    {
        return Ex::sub($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mul(mixed $left, mixed $right): TNumeric
    {
        return Ex::mul($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function div(mixed $left, mixed $right): TNumeric
    {
        return Ex::div($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mod(mixed $left, mixed $right): TNumeric
    {
        return Ex::mod($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function not(mixed $args): TBoolean
    {
        return Ex::not($args);
    }

    /**
     * @inheritDoc
     */
    public function upper(mixed $args): TString
    {
        return Ex::toUpper($args);
    }

    /**
     * @inheritDoc
     */
    public function lower(mixed $args): TString
    {
        return Ex::toLower($args);
    }

    /**
     * @inheritDoc
     */
    public function trim(mixed $args): TString
    {
        return Ex::trim($args);
    }

    /**
     * @inheritDoc
     */
    public function length(mixed $args): TNumeric
    {
        return Ex::length($args);
    }

    /**
     * @inheritDoc
     */
    public function concat(mixed $column, mixed $args): TString
    {
        return Ex::concat($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function contains(mixed $column, mixed $args): TBoolean
    {
        return Ex::contains($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function startsWith(mixed $column, mixed $args): TBoolean
    {
        return Ex::startsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function endsWith(mixed $column, mixed $args): TBoolean
    {
        return Ex::endsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function substring(mixed $column, mixed $start, $end = null): TString
    {
        return Ex::substring($column, $start, $end);
    }

    /**
     * @inheritDoc
     */
    public function indexOf(mixed $column, mixed $args): TNumeric
    {
        return Ex::indexOf($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function pattern(mixed $column, mixed $args): TBoolean
    {
        return Ex::matchesPattern($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function ceil(mixed $args): TNumeric
    {
        return Ex::ceiling($args);
    }

    /**
     * @inheritDoc
     */
    public function floor(mixed $args): TNumeric
    {
        return Ex::floor($args);
    }

    /**
     * @inheritDoc
     */
    public function round(mixed $args): TNumeric
    {
        return Ex::round($args);
    }

    /**
     * @inheritDoc
     */
    public function isNull(mixed $column): TBoolean
    {
        return Ex::eq($column, new Literal(null));
    }

    /**
     * @inheritDoc
     */
    public function isNotNull(mixed $column): TBoolean
    {
        return Ex::ne($column, new Literal(null));
    }

    /**
     * @inheritDoc
     */
    public function literal(mixed $value): Literal
    {
        return Ex::literal($value);
    }

    /**
     * @inheritDoc
     */
    public function date(mixed $column): TNumeric
    {
        return Ex::date($column);
    }

    /**
     * @inheritDoc
     */
    public function day(mixed $column): TNumeric
    {
        return Ex::day($column);
    }

    /**
     * @inheritDoc
     */
    public function hour(mixed $column): TNumeric
    {
        return Ex::hour($column);
    }

    /**
     * @inheritDoc
     */
    public function minute(mixed $column): TNumeric
    {
        return Ex::minute($column);
    }

    /**
     * @inheritDoc
     */
    public function month(mixed $column): TNumeric
    {
        return Ex::month($column);
    }

    /**
     * @inheritDoc
     */
    public function now(): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_NOW));
    }

    /**
     * @inheritDoc
     */
    public function second(mixed $column): TNumeric
    {
        return Ex::second($column);
    }

    /**
     * @inheritDoc
     */
    public function time(mixed $column): TNumeric
    {
        return Ex::time($column);
    }

    /**
     * @inheritDoc
     */
    public function year(mixed $column): TNumeric
    {
        return Ex::year($column);
    }

    /**
     * @inheritDoc
     */
    public function parseIdentifier(string $identifier): Field
    {
        return new Field($identifier);
    }

    /**
     * @inheritDoc
     */
    public function getRequiredJoins(): array
    {
        return [];
    }
}
