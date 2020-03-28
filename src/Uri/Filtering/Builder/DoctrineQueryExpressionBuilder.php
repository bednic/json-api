<?php

declare(strict_types = 1);

namespace JSONAPI\Uri\Filtering\Builder;

use Doctrine\ORM\Query\Expr;
use JSONAPI\Uri\Filtering\Constants;
use JSONAPI\Uri\Filtering\ExpressionBuilder;
use JSONAPI\Uri\Filtering\ExpressionException;
use JSONAPI\Uri\Filtering\Messages;

/**
 * Class DoctrineQueryExpressionBuilder
 *
 * @package JSONAPI\Uri\Filtering
 */
class DoctrineQueryExpressionBuilder implements ExpressionBuilder
{

    private Expr $exp;

    public function __construct()
    {
        if (!class_exists('Doctrine\ORM\Query\Expr')) {
            throw new \RuntimeException(
                'For using ' . __CLASS__ . ' you need install [doctrine/orm] <i>composer require doctrine/orm</i>.'
            );
        }
        $this->exp = new Expr();
    }

    /**
     * @inheritDoc
     */
    public function and($left, $right)
    {
        return $this->exp->andX($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function or($left, $right)
    {
        return $this->exp->orX($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function eq($left, $right)
    {
        return $this->exp->eq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function neq($left, $right)
    {
        return $this->exp->neq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function gt($left, $right)
    {
        return $this->exp->gt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function gte($left, $right)
    {
        return $this->exp->gte($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function lt($left, $right)
    {
        return $this->exp->lt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function lte($left, $right)
    {
        return $this->exp->lte($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function in($column, $args)
    {
        return $this->exp->in($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function add($left, $right)
    {
        return $this->exp->sum($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function sub($left, $right)
    {
        return $this->exp->diff($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mul($left, $right)
    {
        return $this->exp->prod($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function div($left, $right)
    {
        return $this->exp->quot($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mod($left, $right)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::ARITHMETIC_MODULO));
    }

    /**
     * @inheritDoc
     */
    public function not($args)
    {
        return $this->exp->not($args);
    }

    /**
     * @inheritDoc
     */
    public function upper($args)
    {
        return $this->exp->upper($args);
    }

    /**
     * @inheritDoc
     */
    public function lower($args)
    {
        return $this->exp->lower($args);
    }

    /**
     * @inheritDoc
     */
    public function trim($args)
    {
        return $this->exp->trim($args);
    }

    /**
     * @inheritDoc
     */
    public function length($args)
    {
        return $this->exp->length($args);
    }

    /**
     * @inheritDoc
     */
    public function concat($column, $args)
    {
        return $this->exp->concat($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function contains($column, $args)
    {
        return $this->exp->like($column, "'%{$args}%'");
    }

    /**
     * @inheritDoc
     */
    public function startsWith($column, $args)
    {
        return $this->exp->like($column, "'{$args}%'");
    }

    /**
     * @inheritDoc
     */
    public function endsWith($column, $args)
    {
        return $this->exp->like($column, "'%{$args}'");
    }

    /**
     * @inheritDoc
     */
    public function substring($column, $start, $end = null)
    {
        return $this->exp->substring($column, $start, $end);
    }

    /**
     * @inheritDoc
     */
    public function indexOf($column, $args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_INDEX_OF));
    }

    /**
     * @inheritDoc
     */
    public function pattern($column, $args)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_MATCHES_PATTERN)
        );
    }

    /**
     * @inheritDoc
     */
    public function ceil($args)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_CEILING)
        );
    }

    /**
     * @inheritDoc
     */
    public function floor($args)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_FLOOR)
        );
    }

    /**
     * @inheritDoc
     */
    public function round($args)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    /**
     * @inheritDoc
     */
    public function isNull($column)
    {
        return $this->exp->isNull($column);
    }

    /**
     * @inheritDoc
     */
    public function isNotNull($column)
    {
        return $this->exp->isNotNull($column);
    }
}
