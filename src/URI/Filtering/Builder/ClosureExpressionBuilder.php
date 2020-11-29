<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use ExpressionBuilder\Ex;
use ExpressionBuilder\Expression\Field;
use ExpressionBuilder\Expression\Literal;
use JSONAPI\URI\Filtering\Constants;
use JSONAPI\URI\Filtering\ExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\Messages;

class ClosureExpressionBuilder implements ExpressionBuilder, UseDottedIdentifier
{

    /**
     * @inheritDoc
     */
    public function and($left, $right)
    {
        return Ex::and($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function or($left, $right)
    {
        return Ex::or($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function eq($left, $right)
    {
        return Ex::eq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ne($left, $right)
    {
        return Ex::ne($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function gt($left, $right)
    {
        return Ex::gt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ge($left, $right)
    {
        return Ex::ge($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function lt($left, $right)
    {
        return Ex::lt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function le($left, $right)
    {
        return Ex::le($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function in($column, $args)
    {
        return Ex::in($column, new Literal($args));
    }

    /**
     * @inheritDoc
     */
    public function add($left, $right)
    {
        return Ex::add($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function sub($left, $right)
    {
        return Ex::sub($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mul($left, $right)
    {
        return Ex::mul($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function div($left, $right)
    {
        return Ex::div($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mod($left, $right)
    {
        return Ex::mod($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function not($args)
    {
        return Ex::not($args);
    }

    /**
     * @inheritDoc
     */
    public function upper($args)
    {
        return Ex::toUpper($args);
    }

    /**
     * @inheritDoc
     */
    public function lower($args)
    {
        return Ex::toLower($args);
    }

    /**
     * @inheritDoc
     */
    public function trim($args)
    {
        return Ex::trim($args);
    }

    /**
     * @inheritDoc
     */
    public function length($args)
    {
        return Ex::length($args);
    }

    /**
     * @inheritDoc
     */
    public function concat($column, $args)
    {
        return Ex::concat($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function contains($column, $args)
    {
        return Ex::contains($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function startsWith($column, $args)
    {
        return Ex::startsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function endsWith($column, $args)
    {
        return Ex::endsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function substring($column, $start, $end = null)
    {
        return Ex::substring($column, $start, $end);
    }

    /**
     * @inheritDoc
     */
    public function indexOf($column, $args)
    {
        return Ex::indexOf($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function pattern($column, $args)
    {
        return Ex::matchesPattern($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function ceil($args)
    {
        return Ex::ceiling($args);
    }

    /**
     * @inheritDoc
     */
    public function floor($args)
    {
        return Ex::floor($args);
    }

    /**
     * @inheritDoc
     */
    public function round($args)
    {
        return Ex::round($args);
    }

    /**
     * @inheritDoc
     */
    public function isNull($column)
    {
        return Ex::eq($column, new Literal(null));
    }

    /**
     * @inheritDoc
     */
    public function isNotNull($column)
    {
        return Ex::ne($column, new Literal(null));
    }

    /**
     * @inheritDoc
     */
    public function literal($value)
    {
        return Ex::literal($value);
    }

    /**
     * @inheritDoc
     */
    public function date($column)
    {
        return Ex::date($column);
    }

    /**
     * @inheritDoc
     */
    public function day($column)
    {
        return Ex::day($column);
    }

    /**
     * @inheritDoc
     */
    public function hour($column)
    {
        return Ex::hour($column);
    }

    /**
     * @inheritDoc
     */
    public function minute($column)
    {
        return Ex::minute($column);
    }

    /**
     * @inheritDoc
     */
    public function month($column)
    {
        return Ex::month($column);
    }

    /**
     * @inheritDoc
     */
    public function now()
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_NOW));
    }

    /**
     * @inheritDoc
     */
    public function second($column)
    {
        return Ex::second($column);
    }

    /**
     * @inheritDoc
     */
    public function time($column)
    {
        return Ex::time($column);
    }

    /**
     * @inheritDoc
     */
    public function year($column)
    {
        return Ex::year($column);
    }

    /**
     * @inheritDoc
     */
    public function parseIdentifier(string $identifier)
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
