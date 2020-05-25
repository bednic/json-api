<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering;

use DateTime;
use Exception;
use JSONAPI\Exception\Http\BadRequest;
use JSONAPI\Uri\Filtering\Builder\DoctrineCriteriaExpressionBuilder;
use JSONAPI\Uri\Filtering\Builder\UseDottedIdentifier;

/**
 * Class ExpressionFilterParser
 *
 * @package JSONAPI\Uri\Filtering
 */
class ExpressionFilterParser implements FilterInterface, FilterParserInterface
{
    /**
     * @var ExpressionLexer|null
     */
    private ?ExpressionLexer $lexer = null;
    /**
     * @var ExpressionBuilder
     */
    private ExpressionBuilder $exp;

    /**
     * Contains composition of expressions
     *
     * @var mixed
     */
    private $condition;

    /**
     * ExpressionFilterParser constructor.
     *
     * @param ExpressionBuilder $exp
     */
    public function __construct(ExpressionBuilder $exp = null)
    {
        $this->exp = $exp ?? new DoctrineCriteriaExpressionBuilder();
    }

    public function getRequiredJoins(): array
    {
        if ($this->exp instanceof UseDottedIdentifier) {
            return $this->exp->getRequiredJoins();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @inheritDoc
     */
    public function parse($data): FilterInterface
    {
        try {
            if ($data && is_string($data)) {
                $this->lexer = new ExpressionLexer($data);
                $this->condition = $this->parseExpression();
            }
            return $this;
        } catch (ExpressionException $exception) {
            throw new BadRequest($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->lexer ? 'filter=' . rawurlencode($this->lexer->getExpressionText()) : '';
    }

    /**
     * @return mixed Expression
     * @throws ExpressionException
     */
    private function parseExpression()
    {
        return $this->parseLogicalOr();
    }

    /**
     * Parse logical or (or)
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseLogicalOr()
    {
        $left = $this->parseLogicalAnd();
        while ($this->lexer->getCurrentToken()->identifierIs(Constants::LOGICAL_OR)) {
            $this->lexer->nextToken();
            $right = $this->parseLogicalAnd();
            $left = $this->exp->or($left, $right);
        }

        return $left;
    }

    /**
     * Parse logical and (and)
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseLogicalAnd()
    {
        $left = $this->parseComparison();
        while ($this->lexer->getCurrentToken()->identifierIs(Constants::LOGICAL_AND)) {
            $this->lexer->nextToken();
            $right = $this->parseComparison();
            $left = $this->exp->and($left, $right);
        }
        return $left;
    }

    /**
     * Parse comparison operation (eq, ne, gt, gte, lt, lte, in)
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseComparison()
    {
        $left = $this->parseAdditive();
        while ($this->lexer->getCurrentToken()->isComparisonOperator()) {
            $comparisonToken = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            if ($comparisonToken->identifierIs(Constants::LOGICAL_IN)) {
                $right = $this->parseArgumentList();
                $left = $this->exp->{$comparisonToken->text}($left, $right);
            } elseif ($this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::NULL_LITERAL())) {
                if ($comparisonToken->identifierIs(Constants::LOGICAL_EQUAL)) {
                    $left = $this->exp->isNull($left);
                } elseif ($comparisonToken->identifierIs(Constants::LOGICAL_NOT_EQUAL)) {
                    $left = $this->exp->isNotNull($left);
                } else {
                    throw new ExpressionException("Only [eq, neq] operator support NULL comparison.");
                }
                $this->lexer->nextToken();
            } else {
                $right = $this->parseAdditive();
                $left = $this->exp->{$comparisonToken->text}($left, $right);
            }
        }
        return $left;
    }

    /**
     * Parse additive operation (add, sub).
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseAdditive()
    {
        $left = $this->parseMultiplicative();
        while (
            $this->lexer->getCurrentToken()->identifierIs(Constants::ARITHMETIC_ADDITION) ||
            $this->lexer->getCurrentToken()->identifierIs(Constants::ARITHMETIC_SUBTRACTION)
        ) {
            $additiveToken = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            $right = $this->parseMultiplicative();
            $left = $this->exp->{$additiveToken->text}($left, $right);
        }
        return $left;
    }

    /**
     * Parse multiplicative operators (mul, div, mod)
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseMultiplicative()
    {
        $left = $this->parseUnary();
        while (
            $this->lexer->getCurrentToken()->identifierIs(Constants::ARITHMETIC_MULTIPLICATION) ||
            $this->lexer->getCurrentToken()->identifierIs(Constants::ARITHMETIC_DIVISION) ||
            $this->lexer->getCurrentToken()->identifierIs(Constants::ARITHMETIC_MODULO)
        ) {
            $multiplicativeToken = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            $right = $this->parseUnary();
            $left = $this->exp->{$multiplicativeToken->text}($left, $right);
        }
        return $left;
    }

    /**
     * Parse unary operator (- ,not)
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseUnary()
    {
        if (
            $this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::MINUS()) ||
            $this->lexer->getCurrentToken()->identifierIs(Constants::LOGICAL_NOT)
        ) {
            $op = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            if (
                $op->id->equals(ExpressionTokenId::MINUS()) &&
                ExpressionLexer::isNumeric($this->lexer->getCurrentToken()->id)
            ) {
                $numberLiteral = $this->lexer->getCurrentToken();
                $numberLiteral->text = '-' . $numberLiteral->text;
                $numberLiteral->position = $op->position;
                $this->lexer->setCurrentToken($numberLiteral);
                return $this->parsePrimary();
            }

            $expr = $this->parsePrimary();


            if ($op->id->equals(ExpressionTokenId::MINUS())) {
                $expr = '-' . $expr;
            } else {
                $expr = $this->exp->not($expr);
            }
            return $expr;
        }
        return $this->parsePrimary();
    }

    /**
     * @return bool|float|mixed|string|null
     * @throws ExpressionException
     */
    private function parsePrimary()
    {
        return $this->parsePrimaryStart();
    }

    /**
     * @return bool|float|mixed|string|null
     * @throws ExpressionException
     */
    private function parsePrimaryStart()
    {
        switch ($this->lexer->getCurrentToken()->id) {
            case ExpressionTokenId::BOOLEAN_LITERAL():
                return $this->parseBoolean();
            case ExpressionTokenId::DATETIME_LITERAL():
                return $this->parseDatetime();
            case ExpressionTokenId::DECIMAL_LITERAL():
            case ExpressionTokenId::DOUBLE_LITERAL():
            case ExpressionTokenId::SINGLE_LITERAL():
                return $this->parseFloat();
            case ExpressionTokenId::NULL_LITERAL():
                return $this->parseNull();
            case ExpressionTokenId::IDENTIFIER():
                return $this->parseIdentifier();
            case ExpressionTokenId::STRING_LITERAL():
                return $this->parseString();
            case ExpressionTokenId::INT64_LITERAL():
            case ExpressionTokenId::INTEGER_LITERAL():
                return $this->parseInteger();
            case ExpressionTokenId::BINARY_LITERAL():
            case ExpressionTokenId::GUID_LITERAL():
                throw new ExpressionException("Not implemented.");
            case ExpressionTokenId::OPENPARAM():
                return $this->parseParentExpression();
            default:
                throw new ExpressionException("Expression expected.");
        }
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseParentExpression()
    {
        if (!$this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::OPENPARAM())) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        $expr = $this->parseExpression();
        if (!$this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::CLOSEPARAM())) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        return $expr;
    }

    /**
     * @return mixed|string
     * @throws ExpressionException
     */
    private function parseIdentifier()
    {
        if (!$this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::IDENTIFIER())) {
            throw new ExpressionException("Syntax error");
        }
        $isFunction = $this->lexer->peekNextToken()->id->equals(ExpressionTokenId::OPENPARAM());
        if ($isFunction) {
            return $this->parseIdentifierAsFunction();
        } else {
            return $this->parsePropertyAccess();
        }
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseIdentifierAsFunction()
    {
        $functionToken = clone $this->lexer->getCurrentToken();
        $this->lexer->nextToken();
        $params = $this->parseArgumentList();
        if (!method_exists($this->exp, $functionToken->text)) {
            throw new ExpressionException(
                Messages::expressionParserUnknownFunction($functionToken->text, $this->lexer->getPosition())
            );
        }
        return $this->exp->{$functionToken->text}(...$params);
    }

    /**
     * @return array
     * @throws ExpressionException
     */
    private function parseArgumentList()
    {
        if (!$this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::OPENPARAM())) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        $args = $this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::CLOSEPARAM()) ?
            [] : $this->parseArguments();
        if (!$this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::CLOSEPARAM())) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        return $args;
    }

    /**
     * @return array
     * @throws ExpressionException
     */
    private function parseArguments()
    {
        $args = [];
        while (true) {
            $args[] = $this->parseExpression();
            if (!$this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::COMMA())) {
                break;
            }
            $this->lexer->nextToken();
        }
        return $args;
    }

    /**
     * @return string
     * @throws ExpressionException
     */
    private function parsePropertyAccess()
    {
        $property = $this->lexer->readDottedIdentifier();
        if ($this->exp instanceof UseDottedIdentifier) {
            $property = $this->exp->parseIdentifier($property);
        }
        return $property;
    }

    # PARSERS

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseBoolean()
    {
        $value = strcmp($this->lexer->getCurrentToken()->text, Constants::KEYWORD_TRUE) == 0;
        $value = $this->exp->literal($value);
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseDatetime()
    {
        $value = $this->lexer->getCurrentToken()->text;
        try {
            $value = (new DateTime(trim($value, " \t\n\r\0\x0Bdatetime\'")))->format(DATE_ATOM);
            $value = $this->exp->literal($value);
        } catch (Exception $e) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseFloat()
    {
        $value = $this->lexer->getCurrentToken()->text;
        if (($value = filter_var($value, FILTER_VALIDATE_FLOAT)) !== false) {
            $value = $this->exp->literal($value);
            $this->lexer->nextToken();
            return $value;
        }
        throw new ExpressionException("$value is not float.");
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseNull()
    {
        $this->lexer->nextToken();
        return null;
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseString()
    {
        $value = $this->lexer->getCurrentToken()->text;
        $value = trim($value, " \t\n\r\0\x0B\'");
        $value = $this->exp->literal($value);
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseInteger()
    {
        $value = $this->lexer->getCurrentToken()->text;
        if (($value = filter_var($value, FILTER_VALIDATE_INT)) !== false) {
            $value = $this->exp->literal($value);
            $this->lexer->nextToken();
            return $value;
        }
        throw new ExpressionException("$value is not integer.");
    }
}
