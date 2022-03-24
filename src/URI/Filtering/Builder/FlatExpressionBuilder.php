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
use ExpressionBuilder\Expression\Literal;
use ExpressionBuilder\Expression\TBoolean;
use ExpressionBuilder\Expression\TDateTime;
use ExpressionBuilder\Expression\TNumeric;
use ExpressionBuilder\Expression\TString;
use JSONAPI\Data\Collection;
use JSONAPI\URI\Filtering\CanSplitExpression;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\Messages;

/**
 * Class FieldExpressionBuilder
 *
 * @package JSONAPI\URI\Filtering\Quatrodot
 */
class FlatExpressionBuilder extends RichExpressionBuilder implements CanSplitExpression
{
    /**
     * @var Collection fields
     */
    private Collection $fields;

    public function __construct()
    {
        $this->fields = new Collection();
    }

    public function eq(mixed $left, mixed $right): TBoolean
    {
        $ex = Ex::eq($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function ne(mixed $left, mixed $right): TBoolean
    {
        $ex = Ex::ne($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function gt(mixed $left, mixed $right): TBoolean
    {
        $ex = Ex::gt($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function ge(mixed $left, mixed $right): TBoolean
    {
        $ex = Ex::ge($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function lt(mixed $left, mixed $right): TBoolean
    {
        $ex = Ex::lt($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function le(mixed $left, mixed $right): TBoolean
    {
        $ex = Ex::le($left, $right);
        $this->addFieldExpression($left, $ex);
        return $ex;
    }

    public function in(mixed $column, mixed $args): TBoolean
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::LOGICAL_IN));
    }

    public function add(mixed $left, mixed $right): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_ADDITION));
    }

    public function sub(mixed $left, mixed $right): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_SUBTRACTION));
    }

    public function mul(mixed $left, mixed $right): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_MULTIPLICATION));
    }

    public function div(mixed $left, mixed $right): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_DIVISION));
    }

    public function mod(mixed $left, mixed $right): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_MODULO));
    }

    public function not(mixed $args): TBoolean
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::LOGICAL_NOT));
    }

    public function toupper(mixed $args): TString
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TO_UPPER));
    }

    public function tolower(mixed $args): TString
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TO_LOWER));
    }

    public function trim(mixed $args): TString
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TRIM));
    }

    public function length(mixed $args): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_LENGTH));
    }

    public function concat(mixed $column, mixed $args): TString
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_CONCAT));
    }

    public function contains(mixed $column, mixed $args): TBoolean
    {
        $ex = Ex::contains($column, $args);
        $this->addFieldExpression($column, $ex);
        return $ex;
    }

    public function startsWith(mixed $column, mixed $args): TBoolean
    {
        $ex = Ex::startsWith($column, $args);
        $this->addFieldExpression($column, $ex);
        return $ex;
    }

    public function endsWith(mixed $column, mixed $args): TBoolean
    {
        $ex = Ex::endsWith($column, $args);
        $this->addFieldExpression($column, $ex);
        return $ex;
    }

    public function substring(mixed $column, mixed $start, mixed $end = null): TString
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_SUBSTRING));
    }

    public function indexOf(mixed $column, mixed $args): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_INDEX_OF));
    }

    public function pattern(mixed $column, mixed $args): TBoolean
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_MATCHES_PATTERN));
    }

    public function ceil(mixed $args): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_CEILING));
    }

    public function floor(mixed $args): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_FLOOR));
    }

    public function round(mixed $args): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND));
    }

    public function literal(mixed $value): Literal
    {
        return Ex::literal($value);
    }

    public function date(mixed $column): TDateTime
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_DATE));
    }

    public function day(mixed $column): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_DAY));
    }

    public function hour(mixed $column): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_HOUR));
    }

    public function minute(mixed $column): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_MINUTE));
    }

    public function month(mixed $column): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_MONTH));
    }

    public function second(mixed $column): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_SECOND));
    }

    public function time(mixed $column): TDateTime
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_TIME));
    }

    public function year(mixed $column): TNumeric
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_YEAR));
    }

    public function parseIdentifier(string $identifier): Field
    {
        return Ex::field($identifier);
    }

    public function getFieldsExpressions(): Collection
    {
        return $this->fields;
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
}
