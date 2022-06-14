<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\OData;

use DateTime;
use Exception;
use ExpressionBuilder\Ex;
use ExpressionBuilder\Exception\ExpressionBuilderError;
use ExpressionBuilder\Exception\IncomparableExpressions;
use ExpressionBuilder\Exception\InvalidArgument;
use ExpressionBuilder\Expression;
use ExpressionBuilder\Expression\Field;
use ExpressionBuilder\Expression\Literal;
use ExpressionBuilder\Expression\Literal\ArrayValue;
use ExpressionBuilder\Expression\Literal\NullValue;
use ExpressionBuilder\Expression\Type\TArray;
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
use JSONAPI\URI\Path\PathInterface;

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
    public function parse(mixed $data, PathInterface $path): FilterInterface
    {
        $this->lexer = null;
        $this->path  = $path;
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
     * @return TArray|TBoolean|TDateTime|TString|TNumeric
     * @throws ExpressionException
     */
    private function parseLogicalOr(): TArray|TBoolean|TDateTime|TString|TNumeric
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
     * @return TArray|TBoolean|TDateTime|TString|TNumeric
     * @throws ExpressionException
     */
    private function parseLogicalAnd(): TArray|TBoolean|TDateTime|TString|TNumeric
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
     * Parse comparison operation (eq, ne, gt, gte, lt, lte, in, has, be)
     *
     * @return TArray|TBoolean|TDateTime|TString|TNumeric
     * @throws ExpressionException
     */
    private function parseComparison(): TArray|TBoolean|TDateTime|TString|TNumeric
    {
        try {
            $left = $this->parseAdditive();
            while ($this->lexer->getCurrentToken()->isComparisonOperator()) {
                $comparisonToken = clone $this->lexer->getCurrentToken();
                $this->lexer->nextToken();
                if ($comparisonToken->identifierIs(KeyWord::LOGICAL_IN)) {
                    $right = new ArrayValue($this->parseArgumentList());
                    $left  = Ex::in($left, $right);
                } elseif ($comparisonToken->identifierIs(KeyWord::LOGICAL_HAS)) {
                    $right = $this->parsePrimary();
                    $left  = Ex::has($left, $right);
                } elseif ($comparisonToken->identifierIs(KeyWord::LOGICAL_BETWEEN)) {
                    $right = $this->parseArgumentList();
                    $left  = Ex::be($left, ...$right);
                } else {
                    $right = $this->parseAdditive();
                    $left  = Ex::{$comparisonToken->text}($left, $right);
                }
            }
            return $left;
        } catch (InvalidArgument | IncomparableExpressions $exception) {
            throw new ExpressionException($exception->getMessage());
        }
    }

    /**
     * Parse additive operation (add, sub).
     *
     * @return TArray|TBoolean|TDateTime|TString|TNumeric
     * @throws ExpressionException
     */
    private function parseAdditive(): TArray|TBoolean|TDateTime|TString|TNumeric
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
     * @return TArray|TBoolean|TDateTime|TString|TNumeric
     * @throws ExpressionException
     */
    private function parseMultiplicative(): TArray|TBoolean|TDateTime|TString|TNumeric
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
     * @return TArray|TBoolean|TDateTime|TString|TNumeric
     * @throws ExpressionException
     */
    private function parseUnary(): TArray|TBoolean|TDateTime|TString|TNumeric
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
            if ($op->identifierIs(KeyWord::LOGICAL_NOT)) {
                $expr = $this->parseLogicalOr();
                return Ex::not($expr);
            }
        }
        return $this->parsePrimary();
    }

    /**
     * @return TArray|TBoolean|TString|TNumeric|TDateTime
     * @throws ExpressionException
     */
    private function parsePrimary(): TArray|TBoolean|TDateTime|TString|TNumeric
    {
        return match ($this->lexer->getCurrentToken()->id) {
            ExpressionTokenId::BOOLEAN_LITERAL  => $this->parseBoolean(),
            ExpressionTokenId::DATETIME_LITERAL => $this->parseDatetime(),
            ExpressionTokenId::DECIMAL_LITERAL,
            ExpressionTokenId::DOUBLE_LITERAL,
            ExpressionTokenId::SINGLE_LITERAL   => $this->parseFloat(),
            ExpressionTokenId::NULL_LITERAL     => $this->parseNull(),
            ExpressionTokenId::IDENTIFIER       => $this->parseIdentifier(),
            ExpressionTokenId::STRING_LITERAL   => $this->parseString(),
            ExpressionTokenId::INT64_LITERAL,
            ExpressionTokenId::INTEGER_LITERAL  => $this->parseInteger(),
            ExpressionTokenId::BINARY_LITERAL,
            ExpressionTokenId::GUID_LITERAL     => throw new ExpressionException(
                Messages::operandOrFunctionNotImplemented($this->lexer->getCurrentToken()->getIdentifier())
            ),
            ExpressionTokenId::OPEN_PARAM       => $this->parseParentExpression(),
            default                             => throw new ExpressionException("Expression expected.")
        };
    }

    /**
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseBoolean(): TBoolean
    {
        $data = KeyWord::tryFrom($this->lexer->getCurrentToken()->text) === KeyWord::RESERVED_TRUE;
        try {
            /** @var TBoolean $value */
            $value = Ex::literal($data);
        } catch (InvalidArgument $exception) {
            throw new ExpressionException($exception->getMessage());
        }
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
        } catch (Exception) {
            throw new ExpressionException(
                Messages::expressionParserUnrecognizedLiteral(
                    'datetime',
                    $value,
                    $this->lexer->getPosition()
                )
            );
        }
        try {
            $value = Ex::literal($value);
        } catch (InvalidArgument $exception) {
            throw new ExpressionException($exception->getMessage());
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
            try {
                $value = Ex::literal($value);
            } catch (InvalidArgument $exception) {
                throw new ExpressionException($exception->getMessage());
            }
            $this->lexer->nextToken();
            return $value;
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected($this->lexer->getPosition()));
    }

    /**
     * @return NullValue
     * @throws ExpressionException
     */
    private function parseNull(): NullValue
    {
        try {
            /** @var NullValue $value */
            $value = Ex::literal(null);
        } catch (InvalidArgument $exception) {
            throw new ExpressionException($exception->getMessage());
        }
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return TBoolean|TString|TNumeric|TDateTime|TArray
     * @throws ExpressionException
     */
    private function parseIdentifier(): TBoolean|TString|TNumeric|TDateTime|TArray
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
     * @return TBoolean|TString|TNumeric|TDateTime
     * @throws ExpressionException
     */
    private function parseIdentifierAsFunction(): TBoolean|TDateTime|TString|TNumeric
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
     * @return array<TNumeric|TString|TDateTime>
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
     * @return array<TNumeric|TString|TDateTime>
     * @throws ExpressionException
     */
    private function parseArguments(): array
    {
        $args = [];
        while (true) {
            $args[] = $this->parsePrimary();
            if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::COMMA) {
                break;
            }
            $this->lexer->nextToken();
        }
        return $args;
    }

    /**
     * @return TArray|TBoolean|TDateTime|TString|TNumeric
     * @throws ExpressionException
     */
    private function parsePropertyAccess(): TArray|TBoolean|TDateTime|TString|TNumeric
    {
        $identifier = $this->lexer->readDottedIdentifier();
        $type       = 'string';
        try {
            $classMetadata = $this->repository->getByType($this->path->getPrimaryResourceType());
            $parts         = [...explode(".", $identifier)];
            while ($part = array_shift($parts)) {
                if ($classMetadata->hasRelationship($part)) {
                    $classMetadata = $this->repository->getByClass(
                        $classMetadata->getRelationship($part)->target
                    );
                } elseif ($classMetadata->hasAttribute($part)) {
                    $type = $classMetadata->getAttribute($part)->type;
                    if ($type == 'array') {
                        $type = $classMetadata->getAttribute($part)->of . '[]';
                    }
                } elseif ($part === 'id') {
                    $type = 'string';
                } else {
                    throw new ExpressionException(
                        Messages::failedToAccessProperty($part, $classMetadata->getClassName())
                    );
                }
            }
            return Ex::field($identifier, $type);
        } catch (MetadataException | ExpressionBuilderError $exception) {
            throw new ExpressionException(Messages::syntaxError(), previous: $exception);
        }
    }

    /**
     * @return TString
     * @throws ExpressionException
     */
    private function parseString(): TString
    {
        $value = $this->lexer->getCurrentToken()->text;
        $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));
        try {
            $value = Ex::literal((string)$value);
        } catch (InvalidArgument $exception) {
            throw new ExpressionException($exception->getMessage());
        }
        $this->lexer->nextToken();
        return $value;
    }

    /**
     * @return TNumeric
     * @throws ExpressionException
     */
    private function parseInteger(): TNumeric
    {
        $value = $this->lexer->getCurrentToken()->text;

        if (($value = filter_var($value, FILTER_VALIDATE_INT)) !== false) {
            try {
                $value = Ex::literal((int)$value);
            } catch (InvalidArgument $exception) {
                throw new ExpressionException($exception->getMessage());
            }
            $this->lexer->nextToken();
            return $value;
        }

        throw new ExpressionException(Messages::expressionLexerDigitExpected($this->lexer->getPosition()));
    }

    /**
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseParentExpression(): TBoolean
    {
        if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::OPEN_PARAM) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        $expr = $this->parseLogicalOr();
        if ($this->lexer->getCurrentToken()->id !== ExpressionTokenId::CLOSE_PARAM) {
            throw new ExpressionException(Messages::syntaxError());
        }
        $this->lexer->nextToken();
        return $expr;
    }
}
