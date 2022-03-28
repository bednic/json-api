<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\OData;

use DateTime;
use Exception;
use ExpressionBuilder\Ex;
use ExpressionBuilder\Expression;
use ExpressionBuilder\Expression\Field;
use ExpressionBuilder\Expression\Literal;
use ExpressionBuilder\Expression\Type\TBoolean;
use ExpressionBuilder\Expression\Type\TDateTime;
use ExpressionBuilder\Expression\Type\TNumeric;
use ExpressionBuilder\Expression\Type\TString;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\Messages;
use JSONAPI\URI\Filtering\Parser;

/**
 * Class ExpressionFilterParser
 *
 * @package JSONAPI\URI\Filtering
 */
class ExpressionFilterParser extends Parser implements FilterParserInterface
{
    /**
     * @var ExpressionLexer|null
     */
    private ?ExpressionLexer $lexer = null;

    /**
     * @inheritDoc
     */
    public function parse(mixed $data): FilterInterface
    {
        $this->lexer = null;
        if ($data && is_string($data)) {
            $this->lexer = new ExpressionLexer($data);
            $condition   = $this->parseExpression();
            return new ExpressionFilterResult($data, $condition);
        }
        return new ExpressionFilterResult();
    }

    /**
     * @return Expression Expression
     * @throws ExpressionException
     */
    private function parseExpression(): Expression
    {
        return $this->parseLogicalOr();
    }

    /**
     * Parse logical or (or)
     *
     * @return TBoolean|Expression
     * @throws ExpressionException
     */
    private function parseLogicalOr(): TBoolean|Expression
    {
        $left = $this->parseLogicalAnd();
        while ($this->lexer->getCurrentToken()->identifierIs(KeyWord::LOGICAL_OR)) {
            $this->lexer->nextToken();
            $right = $this->parseLogicalAnd();
            $left  = Ex::or($left, $right);
        }

        return $left;
    }

    /**
     * Parse logical and (and)
     *
     * @return TBoolean|Expression
     * @throws ExpressionException
     */
    private function parseLogicalAnd(): TBoolean|Expression
    {
        $left = $this->parseComparison();
        while ($this->lexer->getCurrentToken()->identifierIs(KeyWord::LOGICAL_AND)) {
            $this->lexer->nextToken();
            $right = $this->parseComparison();
            $left  = Ex::and($left, $right);
        }
        return $left;
    }

    /**
     * Parse comparison operation (eq, ne, gt, gte, lt, lte, in, be)
     *
     * @return TBoolean|Expression
     * @throws ExpressionException
     */
    private function parseComparison(): TBoolean|Expression
    {
        $left = $this->parseAdditive();
        while ($this->lexer->getCurrentToken()->isComparisonOperator()) {
            $comparisonToken = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            if ($comparisonToken->identifierIs(KeyWord::LOGICAL_IN)) {
                $right = $this->parseArgumentList();
                $left  = Ex::{$comparisonToken->text}($left, $right);
            } elseif ($comparisonToken->identifierIs(KeyWord::LOGICAL_BETWEEN)) {
                $right = $this->parseArgumentList();
                $left  = Ex::{$comparisonToken->text}($left, ...$right);
            } else {
                $right = $this->parseAdditive();
                $left  = Ex::{$comparisonToken->text}($left, $right);
            }
        }
        return $left;
    }

    /**
     * Parse additive operation (add, sub).
     *
     * @return TNumeric|Expression
     * @throws ExpressionException
     */
    private function parseAdditive(): TNumeric|Expression
    {
        $left = $this->parseMultiplicative();
        while (
            $this->lexer->getCurrentToken()->identifierIs(KeyWord::ARITHMETIC_ADDITION) ||
            $this->lexer->getCurrentToken()->identifierIs(KeyWord::ARITHMETIC_SUBTRACTION)
        ) {
            $token = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            $right = $this->parseMultiplicative();
            $left  = match ($token->getIdentifier()) {
                KeyWord::ARITHMETIC_ADDITION    => Ex::add($left, $right),
                KeyWord::ARITHMETIC_SUBTRACTION => Ex::sub($left, $right),
                default                         => throw new ExpressionException(
                    Messages::operandOrFunctionNotImplemented($token->getIdentifier())
                )
            };
        }
        return $left;
    }

    /**
     * Parse multiplicative operators (mul, div, mod)
     *
     * @return TNumeric|Expression
     * @throws ExpressionException
     */
    private function parseMultiplicative(): TNumeric|Expression
    {
        $left = $this->parseUnary();
        while (
            $this->lexer->getCurrentToken()->identifierIs(KeyWord::ARITHMETIC_MULTIPLICATION) ||
            $this->lexer->getCurrentToken()->identifierIs(KeyWord::ARITHMETIC_DIVISION) ||
            $this->lexer->getCurrentToken()->identifierIs(KeyWord::ARITHMETIC_MODULO)
        ) {
            $token = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            $right = $this->parseUnary();
            $left  = match ($token->getIdentifier()) {
                KeyWord::ARITHMETIC_MULTIPLICATION => Ex::mul($left, $right),
                KeyWord::ARITHMETIC_DIVISION       => Ex::div($left, $right),
                KeyWord::ARITHMETIC_MODULO         => Ex::mod($left, $right),
                default                            => throw new ExpressionException(
                    Messages::operandOrFunctionNotImplemented($token->getIdentifier())
                )
            };
        }
        return $left;
    }

    /**
     * Parse unary operator (- ,not)
     *
     * @return TNumeric|TBoolean|Expression
     * @throws ExpressionException
     */
    private function parseUnary(): TNumeric|TBoolean|Expression
    {
        if (
            $this->lexer->getCurrentToken()->id == ExpressionTokenId::MINUS ||
            $this->lexer->getCurrentToken()->identifierIs(KeyWord::LOGICAL_NOT)
        ) {
            $op = clone $this->lexer->getCurrentToken();
            $this->lexer->nextToken();
            if (
                $op->id === ExpressionTokenId::MINUS &&
                ExpressionLexer::isNumeric($this->lexer->getCurrentToken()->id)
            ) {
                $numberLiteral           = $this->lexer->getCurrentToken();
                $numberLiteral->text     = '-' . $numberLiteral->text;
                $numberLiteral->position = $op->position;
                $this->lexer->setCurrentToken($numberLiteral);
                return $this->parsePrimary();
            }

            $expr = $this->parseExpression();

            if ($op->id === ExpressionTokenId::MINUS) {
                $expr = Ex::literal('-' . $expr->getValue());
            } else {
                $expr = Ex::not($expr);
            }
            return $expr;
        }
        return $this->parsePrimary();
    }

    /**
     * @return TBoolean|TString|TNumeric|TDateTime|Expression
     * @throws ExpressionException
     */
    private function parsePrimary(): TBoolean|TString|TNumeric|TDateTime|Expression
    {
        return $this->parsePrimaryStart();
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parsePrimaryStart(): Expression
    {
        switch ($this->lexer->getCurrentToken()->id) {
            case ExpressionTokenId::BOOLEAN_LITERAL:
                return $this->parseBoolean();
            case ExpressionTokenId::DATETIME_LITERAL:
                return $this->parseDatetime();
            case ExpressionTokenId::DECIMAL_LITERAL:
            case ExpressionTokenId::DOUBLE_LITERAL:
            case ExpressionTokenId::SINGLE_LITERAL:
                return $this->parseFloat();
            case ExpressionTokenId::NULL_LITERAL:
                return $this->parseNull();
            case ExpressionTokenId::IDENTIFIER:
                return $this->parseIdentifier();
            case ExpressionTokenId::STRING_LITERAL:
                return $this->parseString();
            case ExpressionTokenId::INT64_LITERAL:
            case ExpressionTokenId::INTEGER_LITERAL:
                return $this->parseInteger();
            case ExpressionTokenId::BINARY_LITERAL:
            case ExpressionTokenId::GUID_LITERAL:
                throw new ExpressionException(
                    Messages::operandOrFunctionNotImplemented($this->lexer->getCurrentToken()->getIdentifier())
                );
            case ExpressionTokenId::OPEN_PARAM:
                return $this->parseParentExpression();
            default:
                throw new ExpressionException("Expression expected.");
        }
    }

    /**
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseBoolean(): TBoolean
    {
        $value = KeyWord::tryFrom($this->lexer->getCurrentToken()->text) === KeyWord::RESERVED_TRUE;
        $value = Ex::literal($value);
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return TDateTime
     * @throws ExpressionException
     */
    private function parseDatetime(): TDateTime
    {
        $value = $this->lexer->getCurrentToken()->text;
        try {
            $value = new DateTime(trim($value, " \t\n\r\0\x0Bdatetime\'"));
            $value = Ex::literal($value);
        } catch (Exception) {
            throw new ExpressionException(
                Messages::expressionParserUnrecognizedLiteral(
                    'datetime',
                    $value,
                    $this->lexer->getPosition()
                )
            );
        }
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return TNumeric
     * @throws ExpressionException
     */
    private function parseFloat(): TNumeric
    {
        $value = $this->lexer->getCurrentToken()->text;
        if (($value = filter_var($value, FILTER_VALIDATE_FLOAT)) !== false) {
            $value = Ex::literal((float)$value);
            $this->lexer->nextToken();
            return $value;
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected($this->lexer->getPosition()));
    }

    /**
     * @return Literal
     * @throws ExpressionException
     */
    private function parseNull(): Literal
    {
        $value = Ex::literal(null);
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return Field|TString|TNumeric|TBoolean|TDateTime
     * @throws ExpressionException
     */
    private function parseIdentifier(): Field|TString|TNumeric|TBoolean|TDateTime
    {
        if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::IDENTIFIER) {
            throw new ExpressionException(Messages::expressionLexerSyntaxError($this->lexer->getPosition()));
        }
        $isFunction = $this->lexer->peekNextToken()->id === ExpressionTokenId::OPEN_PARAM;
        if ($isFunction) {
            return $this->parseIdentifierAsFunction();
        } else {
            return $this->parsePropertyAccess();
        }
    }

    /**
     * @return TString|TNumeric|TDateTime|TBoolean
     * @throws ExpressionException
     */
    private function parseIdentifierAsFunction(): TString|TNumeric|TDateTime|TBoolean
    {
        $token = clone $this->lexer->getCurrentToken();
        $this->lexer->nextToken();
        $params = $this->parseArgumentList();
        return match ($token->getIdentifier()) {
            KeyWord::FUNCTION_STARTS_WITH     => Ex::startsWith(...$params),
            KeyWord::FUNCTION_ENDS_WITH       => Ex::endsWith(...$params),
            KeyWord::FUNCTION_CONTAINS        => Ex::contains(...$params),
            KeyWord::FUNCTION_CONCAT          => Ex::concat(...$params),
            KeyWord::FUNCTION_INDEX_OF        => Ex::indexOf(...$params),
            KeyWord::FUNCTION_LENGTH          => Ex::length(...$params),
            KeyWord::FUNCTION_SUBSTRING       => Ex::substring(...$params),
            KeyWord::FUNCTION_MATCHES_PATTERN => Ex::matchesPattern(...$params),
            KeyWord::FUNCTION_TO_LOWER        => Ex::toLower(...$params),
            KeyWord::FUNCTION_TO_UPPER        => Ex::toUpper(...$params),
            KeyWord::FUNCTION_TRIM            => Ex::trim(...$params),
            KeyWord::FUNCTION_DATE            => Ex::date(...$params),
            KeyWord::FUNCTION_DAY             => Ex::day(...$params),
            KeyWord::FUNCTION_HOUR            => Ex::hour(...$params),
            KeyWord::FUNCTION_MINUTE          => Ex::minute(...$params),
            KeyWord::FUNCTION_MONTH           => Ex::month(...$params),
            KeyWord::FUNCTION_SECOND          => Ex::second(...$params),
            KeyWord::FUNCTION_TIME            => Ex::time(...$params),
            KeyWord::FUNCTION_YEAR            => Ex::year(...$params),
            KeyWord::FUNCTION_CEILING         => Ex::ceiling(...$params),
            KeyWord::FUNCTION_FLOOR           => Ex::floor(...$params),
            KeyWord::FUNCTION_ROUND           => Ex::round(...$params),
            default                           => throw new ExpressionException(
                Messages::operandOrFunctionNotImplemented($token->getIdentifier())
            )
        };
    }

    /**
     * @return array
     * @throws ExpressionException
     */
    private function parseArgumentList(): array
    {
        if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::OPEN_PARAM) {
            throw new ExpressionException(Messages::expressionLexerSyntaxError($this->lexer->getPosition()));
        }
        $this->lexer->nextToken();
        $args = $this->lexer->getCurrentToken()->id === ExpressionTokenId::CLOSE_PARAM ?
            [] : $this->parseArguments();
        if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::CLOSE_PARAM) {
            throw new ExpressionException(Messages::expressionLexerSyntaxError($this->lexer->getPosition()));
        }
        $this->lexer->nextToken();
        return $args;
    }

    # PARSERS

    /**
     * @return array
     * @throws ExpressionException
     */
    private function parseArguments(): array
    {
        $args = [];
        while (true) {
            $args[] = $this->parseExpression();
            if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::COMMA) {
                break;
            }
            $this->lexer->nextToken();
        }
        return $args;
    }

    /**
     * @return Field
     * @throws ExpressionException
     */
    private function parsePropertyAccess(): Field
    {
        $identifier = $this->lexer->readDottedIdentifier();
        try {
            $classMetadata = $this->repository->getByType($this->path->getPrimaryResourceType());
            $parts         = [...explode(".", $identifier)];
            while ($part = array_shift($parts)) {
                if ($classMetadata->hasRelationship($part)) {
                    $classMetadata = $this->repository->getByClass(
                        $classMetadata->getRelationship($part)->target
                    );
                } elseif ($classMetadata->hasAttribute($part) || $part === 'id') {
                    continue;
                } else {
                    throw new ExpressionException(
                        Messages::failedToAccessProperty($part, $classMetadata->getClassName())
                    );
                }
            }
        } catch (MetadataException $exception) {
            throw new ExpressionException(Messages::syntaxError(), previous: $exception);
        }
        return Ex::field($identifier);
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseString(): TString
    {
        $value = $this->lexer->getCurrentToken()->text;
        $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));
        $value = Ex::literal((string)$value);
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseInteger(): TNumeric
    {
        $value = $this->lexer->getCurrentToken()->text;
        if (($value = filter_var($value, FILTER_VALIDATE_INT)) !== false) {
            $value = Ex::literal((int)$value);
            $this->lexer->nextToken();
            return $value;
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected($this->lexer->getPosition()));
    }

    /**
     * @return mixed
     * @throws ExpressionException
     */
    private function parseParentExpression(): Expression
    {
        if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::OPEN_PARAM) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        $expr = $this->parseExpression();
        if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::CLOSE_PARAM) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        return $expr;
    }
}
