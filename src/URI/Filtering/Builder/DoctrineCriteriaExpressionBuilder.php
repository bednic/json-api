<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\Common\Collections\ExpressionBuilder as Expr;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\ExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\Messages;
use RuntimeException;

/**
 * Class DoctrineCriteriaExpressionBuilder
 *
 * @package JSONAPI\URI\Filtering\Builder
 */
class DoctrineCriteriaExpressionBuilder implements ExpressionBuilder
{
    private Expr $exp;

    public function __construct()
    {
        if (!class_exists('Doctrine\Common\Collections\ExpressionBuilder')) {
            throw new RuntimeException(
                'For using ' . __CLASS__ . ' you need install [doctrine/collection] ' .
                '<i>composer require doctrine/collection</i>.'
            );
        }
        $this->exp = new Expr();
    }

    /**
     * @inheritDoc
     */
    public function and(mixed $left, mixed $right): CompositeExpression
    {
        return $this->exp->andX($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function or(mixed $left, mixed $right): CompositeExpression
    {
        return $this->exp->orX($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function eq(mixed $left, mixed $right): Comparison
    {
        return $this->exp->eq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ne(mixed $left, mixed $right): Comparison
    {
        return $this->exp->neq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function gt(mixed $left, mixed $right): Comparison
    {
        return $this->exp->gt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ge(mixed $left, mixed $right): Comparison
    {
        return $this->exp->gte($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function lt(mixed $left, mixed $right): Comparison
    {
        return $this->exp->lt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function le(mixed $left, mixed $right): Comparison
    {
        return $this->exp->lte($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function in(mixed $column, mixed $args): Comparison
    {
        return $this->exp->in($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function add(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_ADDITION));
    }

    /**
     * @inheritDoc
     */
    public function sub(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_SUBTRACTION));
    }

    /**
     * @inheritDoc
     */
    public function mul(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_MULTIPLICATION));
    }

    /**
     * @inheritDoc
     */
    public function div(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_DIVISION));
    }

    /**
     * @inheritDoc
     */
    public function mod(mixed $left, mixed $right): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_MODULO));
    }

    /**
     * @inheritDoc
     */
    public function not(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::LOGICAL_NOT));
    }

    /**
     * @inheritDoc
     */
    public function toupper(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TO_UPPER));
    }

    /**
     * @inheritDoc
     */
    public function tolower(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TO_LOWER));
    }

    /**
     * @inheritDoc
     */
    public function trim(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TRIM));
    }

    /**
     * @inheritDoc
     */
    public function length(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_LENGTH));
    }

    /**
     * @inheritDoc
     */
    public function concat(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_CONCAT));
    }

    /**
     * @inheritDoc
     */
    public function contains(mixed $column, mixed $args): Comparison
    {
        return $this->exp->contains($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function startsWith(mixed $column, mixed $args): Comparison
    {
        return $this->exp->startsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function endsWith(mixed $column, mixed $args): Comparison
    {
        return $this->exp->endsWith($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function substring(mixed $column, mixed $start, $end = null): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_SUBSTRING));
    }

    /**
     * @inheritDoc
     */
    public function indexOf(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_INDEX_OF));
    }

    /**
     * @inheritDoc
     */
    public function pattern(mixed $column, mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_MATCHES_PATTERN));
    }

    /**
     * @inheritDoc
     */
    public function ceil(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_CEILING));
    }

    /**
     * @inheritDoc
     */
    public function floor(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_FLOOR));
    }

    /**
     * @inheritDoc
     */
    public function round(mixed $args): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND));
    }

    /**
     * @inheritDoc
     */
    public function literal(mixed $value): mixed
    {
        return $value;
    }

    public function date(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_DATE)
        );
    }

    public function day(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_DAY)
        );
    }

    public function hour(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_HOUR)
        );
    }

    public function minute(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_MINUTE)
        );
    }

    public function month(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_MONTH)
        );
    }

    public function second(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_SECOND)
        );
    }

    public function time(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TIME)
        );
    }

    public function year(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_YEAR)
        );
    }

    public function parseIdentifier(string $identifier): mixed
    {
        return $identifier;
    }
}
