<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering;

/**
 * Interface ExpressionBuilder
 * Wraps expected expressions, if you can't implement any of these just throw ExpressionException
 *
 * @package JSONAPI\Uri\Filtering
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
    public function and($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function or($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function eq($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function ne($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function gt($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function ge($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function lt($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function le($left, $right);

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function in($column, $args);

    /**
     * Addition
     *
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function add($left, $right);

    /**
     * Subtraction
     *
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function sub($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function mul($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function div($left, $right);

    /**
     * @param mixed $left
     * @param mixed $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function mod($left, $right);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function not($args);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function upper($args);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function lower($args);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function trim($args);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function length($args);

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function concat($column, $args);

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function contains($column, $args);

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function startsWith($column, $args);

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function endsWith($column, $args);

    /**
     * @param mixed $column
     * @param mixed $start
     * @param mixed $end
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function substring($column, $start, $end = null);

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function indexOf($column, $args);

    /**
     * @param mixed $column
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function pattern($column, $args);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function ceil($args);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function floor($args);

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function round($args);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function isNull($column);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function isNotNull($column);

    /**
     * @param mixed $value
     *
     * @return mixed@throws ExpressionException
     *
     */
    public function literal($value);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function date($column);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function day($column);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function hour($column);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function minute($column);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function month($column);

    /**
     * @return mixed
     */
    public function now();

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function second($column);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function time($column);

    /**
     * @param mixed $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function year($column);
}
