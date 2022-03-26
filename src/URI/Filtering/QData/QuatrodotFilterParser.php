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
use JetBrains\PhpStorm\ArrayShape;
use JSONAPI\Document\Field;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\Attribute;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\Builder\CanSplitExpression;
use JSONAPI\URI\Filtering\Builder\ExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\FilterInterface;
use JSONAPI\URI\Filtering\FilterParserInterface;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\Messages;
use JSONAPI\URI\Path\PathInterface;

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
    /**
     * @var MetadataRepository repository
     */
    private MetadataRepository $repository;
    /**
     * @var PathInterface path
     */
    private PathInterface $path;

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
            $fields    = $this->exp instanceof CanSplitExpression ? $this->exp->getFieldsExpressions() : null;
            return new QuatrodotResult($data, $condition, $fields);
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
        $field = array_shift($expression);
        $op    = array_shift($expression);
        $args  = count($expression) == 1 ? array_shift($expression) : $expression;
        list($left, $attribute) = $this->parseField($field);
        $operand = KeyWord::tryFrom($op);
        $right   = $this->parseArgs($args, $attribute);
        return match ($operand) {
            KeyWord::LOGICAL_EQUAL                 => $this->exp->eq($left, $right),
            KeyWord::LOGICAL_NOT_EQUAL             => $this->exp->ne($left, $right),
            KeyWord::LOGICAL_GREATER_THAN          => $this->exp->gt($left, $right),
            KeyWord::LOGICAL_GREATER_THAN_OR_EQUAL => $this->exp->ge($left, $right),
            KeyWord::LOGICAL_LOWER_THAN            => $this->exp->lt($left, $right),
            KeyWord::LOGICAL_LOWER_THAN_OR_EQUAL   => $this->exp->le($left, $right),
            KeyWord::LOGICAL_IN                    => $this->exp->in($left, $right),
            KeyWord::LOGICAL_BETWEEN               => $this->exp->be($left, $right),
            KeyWord::FUNCTION_CONTAINS             => $this->exp->contains($left, $right),
            KeyWord::FUNCTION_STARTS_WITH          => $this->exp->startsWith($left, $right),
            KeyWord::FUNCTION_ENDS_WITH            => $this->exp->endsWith($left, $right),
            default                                => throw new ExpressionException(
                Messages::operandOrFunctionNotImplemented($op)
            )
        };
    }

    /**
     * @param string $identifier
     *
     * @return array
     * @throws ExpressionException
     */
    private function parseField(string $identifier): array
    {
        $attribute = null;
        try {
            $classMetadata = $this->repository->getByType($this->path->getPrimaryResourceType());
            $parts         = [...explode(".", $identifier)];
            while ($part = array_shift($parts)) {
                if ($classMetadata->hasRelationship($part)) {
                    $identifier    = $classMetadata->getType() . '.' . $part;
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
        } catch (MetadataException $exception) {
            throw new ExpressionException(Messages::syntaxError(), previous: $exception);
        }
        return [$this->exp->field($identifier), $attribute];
    }

    /**
     * @param mixed          $args
     * @param Attribute|null $attribute
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function parseArgs(mixed $args, ?Attribute $attribute): mixed
    {
        if (!is_null($attribute)) {
            $args = match ($attribute->type) {
                'int'           => $this->parseInt($args),
                'bool'          => $this->parseBoolean($args),
                'float'         => $this->parseFloat($args),
                'array'         => $this->parseArray($args, $attribute),
                DateTimeInterface::class, DateTimeImmutable::class,
                DateTime::class => $this->parseDate($args),
                default         => $args
            };
        }
        return $this->exp->literal($args);
    }

    /**
     * @param mixed $args
     *
     * @return int
     * @throws ExpressionException
     */
    private function parseInt(mixed $args): int
    {
        if (($value = filter_var($args, FILTER_VALIDATE_INT)) !== false) {
            return $value;
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected(0));
    }

    /**
     * @param mixed $args
     *
     * @return bool
     * @throws ExpressionException
     */
    private function parseBoolean(mixed $args): bool
    {
        if (($value = filter_var($args, FILTER_VALIDATE_BOOL)) !== false) {
            return $value;
        }
        throw new ExpressionException(Messages::expressionLexerBooleanExpected(0));
    }

    /**
     * @param mixed $args
     *
     * @return float
     * @throws ExpressionException
     */
    private function parseFloat(mixed $args): float
    {
        if (($value = filter_var($args, FILTER_VALIDATE_FLOAT)) !== false) {
            return $value;
        }
        throw new ExpressionException(Messages::expressionLexerDigitExpected(0));
    }

    /**
     * @param mixed     $args
     * @param Attribute $attribute
     *
     * @return array<mixed>
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
     * @return DateTimeInterface
     * @throws ExpressionException
     */
    private function parseDate(mixed $args): DateTimeInterface
    {
        try {
            return new DateTimeImmutable($args);
        } catch (Exception $e) {
            throw new ExpressionException(Messages::expressionLexerUnterminatedStringLiteral(0, $args));
        }
    }

    /**
     * eq, ne, gt, ge, lt, le, be
     *
     * @return mixed
     */
    private function parseLogical(): mixed
    {
        return match ($operand) {
            KeyWord::LOGICAL_EQUAL                 => $this->exp->eq($left, $right),
            KeyWord::LOGICAL_NOT_EQUAL             => $this->exp->ne($left, $right),
            KeyWord::LOGICAL_GREATER_THAN          => $this->exp->gt($left, $right),
            KeyWord::LOGICAL_GREATER_THAN_OR_EQUAL => $this->exp->ge($left, $right),
            KeyWord::LOGICAL_LOWER_THAN            => $this->exp->lt($left, $right),
            KeyWord::LOGICAL_LOWER_THAN_OR_EQUAL   => $this->exp->le($left, $right),
            KeyWord::LOGICAL_IN                    => $this->exp->in($left, $right),
            KeyWord::LOGICAL_BETWEEN               => $this->exp->be($left, $right),
            default                                => throw new ExpressionException(
                Messages::operandOrFunctionNotImplemented($op)
            )
        };
    }

    /**
     * contains, startsWith, endsWith
     *
     * @return mixed
     */
    private function parseStringFunction(array $expression): mixed
    {
        $field   = array_shift($expression);
        $op      = array_shift($expression);
        $args    = array_shift($expression);
        $left    = $this->parseField($field);
        $operand = KeyWord::tryFrom($op);
        $right   = $this->parseArgs($args, null);
        return match ($operand) {
            KeyWord::FUNCTION_CONTAINS    => $this->exp->contains($left, $right),
            KeyWord::FUNCTION_STARTS_WITH => $this->exp->startsWith($left, $right),
            KeyWord::FUNCTION_ENDS_WITH   => $this->exp->endsWith($left, $right),
            default                       => throw new ExpressionException(
                Messages::operandOrFunctionNotImplemented($op)
            )
        };
    }
}
