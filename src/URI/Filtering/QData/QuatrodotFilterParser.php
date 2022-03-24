<?php

/**
 * Created by uzivatel
 * at 22.03.2022 15:03
 */

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\QData;

use JSONAPI\URI\Filtering\CanSplitExpression;
use JSONAPI\URI\Filtering\ExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\Messages;
use JSONAPI\URI\Filtering\UseDottedIdentifier;

/**
 * Class FilterParser
 *
 * @package JSONAPI\URI\Filtering\Quatrodot
 */
class QuatrodotFilterParser implements FilterParserInterface
{
    /**
     * @var ExpressionBuilder exp
     */
    private ExpressionBuilder $exp;

    public function __construct(ExpressionBuilder $expressionBuilder)
    {
        $this->exp = $expressionBuilder;
    }

    public function parse(mixed $data): FilterInterface
    {
        if (is_string($data)) {
            $phrases = explode(KeyWord::PHRASE_SEPARATOR->value, $data);
            $tree    = [];
            foreach ($phrases as $phrase) {
                $tokens         = explode(KeyWord::VALUE_SEPARATOR->value, $phrase);
                $field          = array_shift($tokens);
                $op             = array_shift($tokens);
                $args           = $tokens;
                $tree[$field][] = [$field, $op, $args];
            }
            $condition = $this->parseAnd($tree);
            $joins     = $this->exp instanceof UseDottedIdentifier ? $this->exp->getRequiredJoins() : null;
            $fields    = $this->exp instanceof CanSplitExpression ? $this->exp->getFieldsExpressions() : null;
            return new QuatrodotResult($data, $condition, $joins, $fields);
        }
        return new QuatrodotResult();
    }

    /**
     * @throws ExpressionException
     */
    private function parseAnd(array $tree): mixed
    {
        $left = null;
        foreach ($tree as $expressions) {
            $right = $this->parseOr($expressions);
            if ($left) {
                $left = $this->exp->and($left, $right);
            } else {
                $left = $right;
            }
        }
        return $left;
    }

    /**
     * @throws ExpressionException
     */
    private function parseOr(array $expressions): mixed
    {
        $left = null;
        foreach ($expressions as $expression) {
            $right = $this->parseExpression($expression);
            if ($left) {
                $left = $this->exp->or($left, $right);
            } else {
                $left = $right;
            }
        }
        return $left;
    }

    /**
     * @throws ExpressionException
     */
    private function parseExpression(array $expression): mixed
    {
        $field   = array_shift($expression);
        $op      = array_shift($expression);
        $args    = count($expression) == 1 ? array_shift($expression) : $expression;
        $left    = $this->parseField($field);
        $operand = KeyWord::tryFrom($op);
        $right   = $this->parseArgs($args);
        return match ($operand) {
            KeyWord::LOGICAL_EQUAL                 => $this->exp->eq($left, $right),
            KeyWord::LOGICAL_NOT_EQUAL             => $this->exp->ne($left, $right),
            KeyWord::LOGICAL_GREATER_THAN          => $this->exp->gt($left, $right),
            KeyWord::LOGICAL_GREATER_THAN_OR_EQUAL => $this->exp->ge($left, $right),
            KeyWord::LOGICAL_LOWER_THAN            => $this->exp->lt($left, $right),
            KeyWord::LOGICAL_LOWER_THAN_OR_EQUAL   => $this->exp->le($left, $right),
            KeyWord::FUNCTION_CONTAINS             => $this->exp->contains($left, $right),
            KeyWord::FUNCTION_STARTS_WITH          => $this->exp->startsWith($left, $right),
            KeyWord::FUNCTION_ENDS_WITH            => $this->exp->endsWith($left, $right),
            default                                => throw new ExpressionException(
                Messages::operandOrFunctionNotImplemented($op)
            )
        };
    }

    /**
     * @param mixed $fieldName
     *
     * @return mixed
     */
    private function parseField(string $fieldName): mixed
    {
        return $this->exp->parseIdentifier($fieldName);
    }

    /**
     * @param mixed $args
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseArgs(mixed $args): mixed
    {
        return $this->exp->literal($args);
    }
}
