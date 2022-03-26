<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use JSONAPI\URI\Filtering\ExpressionException;

/**
 * Interface ExpressionBuilder
 * Wraps expected expressions, if you can't implement any of these just throw ExpressionException
 *
 * @package JSONAPI\URI\Filtering
 */
interface ExpressionBuilder
{
    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function and(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function or(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function eq(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function ne(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function gt(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function ge(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function lt(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function le(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function in(mixed $column, mixed $args): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function be(mixed $column, mixed $args): mixed;

    /**
     * Addition
     *
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function add(mixed $left, mixed $right): mixed;

    /**
     * Subtraction
     *
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function sub(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function mul(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function div(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function mod(mixed $left, mixed $right): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function not(mixed $args): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function toupper(mixed $args): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function tolower(mixed $args): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function trim(mixed $args): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function length(mixed $args): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function concat(mixed $column, mixed $args): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function contains(mixed $column, mixed $args): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function startsWith(mixed $column, mixed $args): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function endsWith(mixed $column, mixed $args): mixed;

    /**
     * @param mixed $column
     * @param mixed $start
     * @param mixed $end
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function substring(mixed $column, mixed $start, mixed $end = null): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function indexOf(mixed $column, mixed $args): mixed;

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function pattern(mixed $column, mixed $args): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function ceil(mixed $args): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function floor(mixed $args): mixed;

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function round(mixed $args): mixed;

    /**
     * @param mixed $value
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function literal(mixed $value): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function date(mixed $column): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function day(mixed $column): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function hour(mixed $column): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function minute(mixed $column): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function month(mixed $column): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function second(mixed $column): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function time(mixed $column): mixed;

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function year(mixed $column): mixed;

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function field(mixed $identifier): mixed;
}
