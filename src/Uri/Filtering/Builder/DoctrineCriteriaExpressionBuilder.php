<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering\Builder;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\Common\Collections\ExpressionBuilder as Expr;
use JSONAPI\Uri\Filtering\Constants;
use JSONAPI\Uri\Filtering\ExpressionBuilder;
use JSONAPI\Uri\Filtering\ExpressionException;
use JSONAPI\Uri\Filtering\Messages;

/**
 * Class DoctrineCriteriaExpressionBuilder
 *
 * @package JSONAPI\Uri\Filtering\Builder
 */
class DoctrineCriteriaExpressionBuilder implements ExpressionBuilder
{

    private Expr $exp;

    public function __construct()
    {
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
    public function ne($left, $right)
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
    public function ge($left, $right)
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
    public function le($left, $right)
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
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::ARITHMETIC_ADDITION));
    }

    /**
     * @inheritDoc
     */
    public function sub($left, $right)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::ARITHMETIC_SUBTRACTION));
    }

    /**
     * @inheritDoc
     */
    public function mul($left, $right)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::ARITHMETIC_MULTIPLICATION));
    }

    /**
     * @inheritDoc
     */
    public function div($left, $right)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::ARITHMETIC_DIVISION));
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
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::LOGICAL_NOT));
    }

    /**
     * @inheritDoc
     */
    public function upper($args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_TO_UPPER));
    }

    /**
     * @inheritDoc
     */
    public function lower($args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_TO_LOWER));
    }

    /**
     * @inheritDoc
     */
    public function trim($args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_TRIM));
    }

    /**
     * @inheritDoc
     */
    public function length($args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_LENGTH));
    }

    /**
     * @inheritDoc
     */
    public function concat($column, $args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_CONCAT));
    }

    /**
     * @inheritDoc
     */
    public function contains($column, $args)
    {
        return $this->exp->contains($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function startsWith($column, $args)
    {
        return $this->exp->startsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function endsWith($column, $args)
    {
        return $this->exp->endsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function substring($column, $start, $end = null)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_SUBSTRING));
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
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_MATCHES_PATTERN));
    }

    /**
     * @inheritDoc
     */
    public function ceil($args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_CEILING));
    }

    /**
     * @inheritDoc
     */
    public function floor($args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_FLOOR));
    }

    /**
     * @inheritDoc
     */
    public function round($args)
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND));
    }

    /**
     * @inheritDoc
     */
    public function isNull($column)
    {
        return $this->exp->isNull($column);
    }

    public function isNotNull($column)
    {
        return new Comparison($column, Comparison::NEQ, new Value(null));
    }

    /**
     * @inheritDoc
     */
    public function literal($value)
    {
        return $value;
    }

    public function date($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function day($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function hour($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function minute($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function month($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function now()
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function second($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function time($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }

    public function year($column)
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_ROUND)
        );
    }
}
