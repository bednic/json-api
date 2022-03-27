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
use ExpressionBuilder\Expression;
use ExpressionBuilder\Expression\Field;
use ExpressionBuilder\Expression\Literal;
use ExpressionBuilder\Expression\Type\TBoolean;
use ExpressionBuilder\Expression\Type\TDateTime;
use ExpressionBuilder\Expression\Type\TNumeric;
use JSONAPI\Data\Collection;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\Attribute;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\Messages;
use JSONAPI\URI\Filtering\Parser;

/**
 * Class FilterParser
 *
 * @package JSONAPI\URI\Filtering\Quatrodot
 */
class QuatrodotFilterParser extends Parser implements FilterParserInterface
{
    /**
     * @var Collection
     */
    private Collection $fieldsExpressions;

    public function parse(mixed $data): FilterInterface
    {
        if (is_string($data)) {
            $this->fieldsExpressions = new Collection();
            $phrases                 = explode(KeyWord::PHRASE_SEPARATOR->value, $data);
            $tree                    = [];
            foreach ($phrases as $phrase) {
                $tokens = explode(KeyWord::VALUE_SEPARATOR->value, $phrase);
                $field          = array_shift($tokens);
                $op             = array_shift($tokens);
                $args           = $tokens;
                $tree[$field][] = [$field, $op, $args];
            }
            $condition = $this->parseExpression($tree);
            return new QuatrodotResult($data, $condition, $this->fieldsExpressions);
        }
        return new QuatrodotResult();
    }

    /**
     * @return Expression Expression
     * @throws ExpressionException
     */
    private function parseExpression(array $expression): Expression
    {
        return $this->parseAnd($expression);
    }

    /**
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
     * @throws ExpressionException
     */
    private function parseComparison(array $expression): TBoolean
    {
        $field   = array_shift($expression);
        $op      = array_shift($expression);
        $args    = array_shift($expression);
        $left    = $this->parseField($field);
        $operand = KeyWord::tryFrom($op);
        $right   = $this->parseArgs($args, $this->getAttributeForIdentifier($field));
        $ex      = match ($operand) {
            KeyWord::LOGICAL_EQUAL                 => Ex::eq($left, $right),
            KeyWord::LOGICAL_NOT_EQUAL             => Ex::ne($left, $right),
            KeyWord::LOGICAL_GREATER_THAN          => Ex::gt($left, $right),
            KeyWord::LOGICAL_GREATER_THAN_OR_EQUAL => Ex::ge($left, $right),
            KeyWord::LOGICAL_LOWER_THAN            => Ex::lt($left, $right),
            KeyWord::LOGICAL_LOWER_THAN_OR_EQUAL   => Ex::le($left, $right),
            KeyWord::LOGICAL_IN                    => Ex::in($left, $right),
            KeyWord::LOGICAL_BETWEEN               => Ex::be($left, ...$right),
            KeyWord::FUNCTION_CONTAINS             => Ex::contains($left, $right),
            KeyWord::FUNCTION_STARTS_WITH          => Ex::startsWith($left, $right),
            KeyWord::FUNCTION_ENDS_WITH            => Ex::endsWith($left, $right),
            default                                => throw new ExpressionException(
                Messages::operandOrFunctionNotImplemented($op)
            )
        };
        if ($this->fieldsExpressions->hasKey($field)) {
            $old = $this->fieldsExpressions->get($field);
            $new = Ex::or($old, $ex);
            $this->fieldsExpressions->set($field, $new);
        } else {
            $this->fieldsExpressions->set($field, $ex);
        }
        return $ex;
    }

    /**
     * @param string $identifier
     *
     * @return Field
     * @throws ExpressionException
     */
    private function parseField(string $identifier): Field
    {
        $this->getAttributeForIdentifier($identifier);
        return Ex::field($identifier);
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
                } elseif ($classMetadata->hasAttribute($part) || $part === \JSONAPI\Document\Field::ID) {
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
     * @param mixed          $args
     * @param Attribute|null $attribute
     *
     * @return Literal|Literal[]
     * @throws ExpressionException
     */
    private function parseArgs(mixed $args, ?Attribute $attribute): Literal|array
    {
        if (!is_null($attribute)) {
            return match ($attribute->type) {
                'int'           => $this->parseInt($args),
                'bool'          => $this->parseBoolean($args),
                'float'         => $this->parseFloat($args),
                'array'         => $this->parseArray($args, $attribute),
                DateTimeInterface::class, DateTimeImmutable::class,
                DateTime::class => $this->parseDate($args),
                default         => Ex::literal($args)
            };
        }
        return Ex::literal($args);
    }

    /**
     * @param mixed $args
     *
     * @return TNumeric
     * @throws ExpressionException
     */
    private function parseInt(mixed $args): TNumeric
    {
        if (($value = filter_var(self::singleArgument($args), FILTER_VALIDATE_INT)) !== false) {
            return Ex::literal($value);
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected(0));
    }

    /**
     * @param mixed $args
     *
     * @return TBoolean
     * @throws ExpressionException
     */
    private function parseBoolean(mixed $args): TBoolean
    {
        if (($value = filter_var(self::singleArgument($args), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)) !== null) {
            return Ex::literal($value);
        }
        throw new ExpressionException(Messages::expressionLexerBooleanExpected(0));
    }

    /**
     * @param mixed $args
     *
     * @return TNumeric
     * @throws ExpressionException
     */
    private function parseFloat(mixed $args): TNumeric
    {
        if (($value = filter_var(self::singleArgument($args), FILTER_VALIDATE_FLOAT)) !== false) {
            return Ex::literal($value);
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected(0));
    }

    /**
     * @param mixed     $args
     * @param Attribute $attribute
     *
     * @return Literal[]
     * @throws ExpressionException
     */
    private function parseArray(mixed $args, Attribute $attribute): array
    {
        $ret             = [];
        $attribute->type = $attribute->of;
        foreach ($args as $arg) {
            $ret[] = $this->parseArgs($arg, $attribute);
        }
        return $ret;
    }

    /**
     * @param mixed $args
     *
     * @return TDateTime
     * @throws ExpressionException
     */
    private function parseDate(mixed $args): TDateTime
    {
        try {
            return Ex::literal(new DateTimeImmutable(self::singleArgument($args)));
        } catch (Exception $e) {
            throw new ExpressionException(Messages::expressionLexerUnterminatedStringLiteral(0, $args));
        }
    }

    /**
     * @param array $args
     *
     * @return string|bool|int|float|DateTimeInterface|null
     * @throws ExpressionException
     */
    private static function singleArgument(array $args): string|bool|int|float|null|DateTimeInterface
    {
        if (count($args) != 1) {
            throw new ExpressionException(/*todo*/);
        }
        return $args[0];
    }
}
