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
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function and($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function or($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function eq($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function neq($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function gt($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function gte($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function lt($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function lte($left, $right);

    /**
     * @param $column
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function in($column, $args);

    /**
     * Addition
     *
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function add($left, $right);

    /**
     * Subtraction
     *
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function sub($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function mul($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function div($left, $right);

    /**
     * @param $left
     * @param $right
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function mod($left, $right);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function not($args);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function upper($args);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function lower($args);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function trim($args);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function length($args);

    /**
     * @param $column
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function concat($column, $args);

    /**
     * @param $column
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function contains($column, $args);

    /**
     * @param $column
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function startsWith($column, $args);

    /**
     * @param $column
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function endsWith($column, $args);

    /**
     * @param      $column
     * @param      $start
     * @param null $end
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function substring($column, $start, $end = null);

    /**
     * @param $column
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function indexOf($column, $args);

    /**
     * @param $column
     * @param $args
     *
     * @return mixed
     */
    public function pattern($column, $args);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function ceil($args);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function floor($args);

    /**
     * @param $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function round($args);

    /**
     * @param $column
     *
     * @return mixed
     * @throws ExpressionException
     */
    public function isNull($column);

    public function isNotNull($column);


    public static function useDotedIdentifier(): bool;
}
