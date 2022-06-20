<?php

/**
 * Created by uzivatel
 * at 22.03.2022 15:03
 */

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\QData;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use ExpressionBuilder\Ex;
use ExpressionBuilder\Exception\IncomparableExpressions;
use ExpressionBuilder\Exception\InvalidArgument;
use ExpressionBuilder\Expression;
use ExpressionBuilder\Expression\Literal\ArrayValue;
use ExpressionBuilder\Expression\Type\TBoolean;
use ExpressionBuilder\Expression\Type\TDateTime;
use ExpressionBuilder\Expression\Type\TNumeric;
use ExpressionBuilder\Expression\Type\TString;
use JSONAPI\Document\Field;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\Attribute;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\Messages;
use JSONAPI\URI\Filtering\Parser;
use JSONAPI\URI\Path\PathInterface;

/**
 * Class FilterParser
 *
 * @package JSONAPI\URI\Filtering\Quatrodot
 */
class QuatrodotFilterParser extends Parser implements FilterParserInterface
{
    /**
     * @var QuatrodotResult
     */
    private QuatrodotResult $result;

    /**
     * @param mixed         $data
     * @param PathInterface $path
     *
     * @return FilterInterface
     * @throws ExpressionException
     */
    public function parse(mixed $data, PathInterface $path): FilterInterface
    {
        $this->path   = $path;
        $this->result = new QuatrodotResult();
        if (is_string($data)) {
            $this->result->setOrigin($data);
            $phrases = explode(KeyWord::PHRASE_SEPARATOR->value, $data);
            $tree    = [];
            foreach ($phrases as $phrase) {
                $tokens = explode(KeyWord::VALUE_SEPARATOR->value, $phrase);
                $field  = array_shift($tokens);
                $op     = array_shift($tokens);
                $args   = $tokens;
                if ($field && $op && $args) {
                    $tree[$field][] = [$field, $op, $args];
                } else {
                    throw new ExpressionException(Messages::syntaxError());
                }
            }
            $condition = $this->parseExpression($tree);
            $this->result->setCondition($condition);
        }
        return $this->result;
    }

    /**
     * @param array<string, array<int, array<int,array<int, string>|string>>> $expression
     *
     * @return Expression
     * @throws ExpressionException
     */
    private function parseExpression(array $expression): Expression
    {
        return $this->parseAnd($expression);
    }

    /**
     * @param array<array<string|array<string>>> $expressionTree
     *
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseAnd(array $expressionTree): TBoolean
    {
        $left = null;
        foreach ($expressionTree as $expressions) {
            $right = $this->parseOr($expressions);
            if ($left) {
                $left = Ex::and($left, $right);
            } else {
                $left = $right;
            }
        }
        return $left;
    }

    /**
     * @param array<string|array<string>> $expressions
     *
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseOr(array $expressions): TBoolean
    {
        $left = null;
        foreach ($expressions as $expression) {
            $right = $this->parseComparison($expression);
            if ($left) {
                $left = Ex::or($left, $right);
            } else {
                $left = $right;
            }
        }
        return $left;
    }

    /**
     * @param array<string|array<string>> $expression
     *
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseComparison(array $expression): TBoolean
    {
        try {
            $field   = array_shift($expression);
            $op      = array_shift($expression);
            $args    = array_shift($expression);
            $left    = $this->parseField($field);
            $operand = KeyWord::tryFrom($op);
            $right   = $this->parseArgs($args, $this->getAttributeForIdentifier($field));
            $ex      = match ($operand) {
                KeyWord::LOGICAL_EQUAL                 => Ex::eq($left, ...$right),
                KeyWord::LOGICAL_NOT_EQUAL             => Ex::ne($left, ...$right),
                KeyWord::LOGICAL_GREATER_THAN          => Ex::gt($left, ...$right),
                KeyWord::LOGICAL_GREATER_THAN_OR_EQUAL => Ex::ge($left, ...$right),
                KeyWord::LOGICAL_LOWER_THAN            => Ex::lt($left, ...$right),
                KeyWord::LOGICAL_LOWER_THAN_OR_EQUAL   => Ex::le($left, ...$right),
                KeyWord::LOGICAL_IN                    => Ex::in($left, new ArrayValue($right)),
                KeyWord::LOGICAL_BETWEEN               => Ex::be($left, ...$right),
                KeyWord::FUNCTION_CONTAINS             => Ex::contains($left, ...$right),
                KeyWord::FUNCTION_STARTS_WITH          => Ex::startsWith($left, ...$right),
                KeyWord::FUNCTION_ENDS_WITH            => Ex::endsWith($left, ...$right),
                default                                => throw new ExpressionException(
                    Messages::operandOrFunctionNotImplemented($operand)
                )
            };
            $this->result->addConditionFor($field, $ex);
            return $ex;
        } catch (InvalidArgument | IncomparableExpressions $exception) {
            throw new ExpressionException($exception->getMessage());
        }
    }

    /**
     * @param string $identifier
     *
     * @return TBoolean|TString|TNumeric|TDateTime
     * @throws ExpressionException
     */
    private function parseField(string $identifier): TBoolean|TString|TNumeric|TDateTime
    {
        $attribute = $this->getAttributeForIdentifier($identifier);
        try {
            $type = $attribute->type ?? 'string';
            if ($type == 'array') {
                $type = $attribute->of . '[]';
            } else {
                try {
                    if ((new \ReflectionClass($type))->isIterable()) {
                        $type = $attribute->of . '[]';
                    }
                } catch (\ReflectionException $exception) {
                    // class does not exist
                }
            }
            return Ex::field($identifier, $type);
        } catch (InvalidArgument $exception) {
            throw new ExpressionException(Messages::syntaxError(), previous: $exception);
        }
    }

    /**
     * @param string $identifier
     *
     * @return Attribute
     * @throws ExpressionException
     */
    private function getAttributeForIdentifier(string $identifier): Attribute
    {
        try {
            $attribute     = null;
            $classMetadata = $this->repository->getByType($this->path->getPrimaryResourceType());
            $parts         = [...explode(".", $identifier)];
            while ($part = array_shift($parts)) {
                if ($classMetadata->hasRelationship($part)) {
                    $classMetadata = $this->repository->getByClass(
                        $classMetadata->getRelationship($part)->target
                    );
                } elseif ($classMetadata->hasAttribute($part) || $part === Field::ID) {
                    $attribute = $classMetadata->getAttribute($part);
                } else {
                    throw new ExpressionException(
                        Messages::failedToAccessProperty($part, $classMetadata->getClassName())
                    );
                }
            }
            return $attribute;
        } catch (MetadataException $exception) {
            throw new ExpressionException(Messages::syntaxError(), previous: $exception);
        }
    }

    /**
     * @param string[]|string[][] $args
     * @param Attribute|null      $attribute
     *
     * @return array<TBoolean|TString|TNumeric|TDateTime>
     * @throws ExpressionException
     */
    private function parseArgs(mixed $args, ?Attribute $attribute): array
    {
        try {
            if (!is_null($attribute)) {
                $type = $attribute->type;
                if ($attribute->type === 'array') {
                    $type = $attribute->of;
                }
                $params = [];
                foreach ($args as $arg) {
                    $params[] = match ($type) {
                        'int', 'integer'  => $this->parseInt($arg),
                        'bool', 'boolean' => $this->parseBoolean($arg),
                        'float', 'double' => $this->parseFloat($arg),
                        DateTimeInterface::class,
                        DateTimeImmutable::class,
                        DateTime::class   => $this->parseDate($arg),
                        default           => Ex::literal($arg)
                    };
                }
                return $params;
            } else {
                return [Ex::literal($args)];
            }
        } catch (InvalidArgument $exception) {
            throw new ExpressionException($exception->getMessage());
        }
    }

    /**
     * @param string $arg
     *
     * @return TNumeric
     * @throws ExpressionException
     */
    private function parseInt(string $arg): TNumeric
    {
        if (($value = filter_var($arg, FILTER_VALIDATE_INT)) !== false) {
            try {
                return Ex::literal($value);
            } catch (InvalidArgument $exception) {
                throw new ExpressionException($exception->getMessage());
            }
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected(0));
    }

    /**
     * @param string $arg
     *
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseBoolean(string $arg): TBoolean
    {
        if (($value = filter_var($arg, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) !== null) {
            try {
                return Ex::literal($value);
            } catch (InvalidArgument $exception) {
                throw new ExpressionException($exception->getMessage());
            }
        }
        throw new ExpressionException(Messages::expressionLexerBooleanExpected(0));
    }

    /**
     * @param string $arg
     *
     * @return TNumeric
     * @throws ExpressionException
     */
    private function parseFloat(string $arg): TNumeric
    {
        if (($value = filter_var($arg, FILTER_VALIDATE_FLOAT)) !== false) {
            try {
                return Ex::literal($value);
            } catch (InvalidArgument $exception) {
                throw new ExpressionException($exception->getMessage());
            }
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected(0));
    }

    /**
     * @param string $args
     *
     * @return TDateTime
     * @throws ExpressionException
     */
    private function parseDate(string $args): TDateTime
    {
        try {
            $data = new DateTimeImmutable($args);
        } catch (Exception $e) {
            throw new ExpressionException(
                Messages::expressionParserUnrecognizedLiteral('datetime', $args, 0),
                previous: $e
            );
        }
        try {
            return Ex::literal($data);
        } catch (InvalidArgument $exception) {
            throw new ExpressionException($exception->getMessage());
        }
    }
}
