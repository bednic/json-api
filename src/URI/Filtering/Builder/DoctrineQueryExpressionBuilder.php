<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use DateTimeInterface;
use Doctrine\ORM\Query\Expr;
use JSONAPI\Data\Collection;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Exception\Metadata\MetadataNotFound;
use JSONAPI\Exception\Metadata\RelationNotFound;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\KeyWord;
use JSONAPI\URI\Filtering\Messages;
use JSONAPI\URI\Path\PathInterface;
use RuntimeException;

/**
 * Class DoctrineQueryExpressionBuilder
 *
 * @package JSONAPI\URI\Filtering\Builder
 */
class DoctrineQueryExpressionBuilder implements ExpressionBuilder
{
    /**
     * @var Expr
     */
    private Expr $exp;
    /**
     * @var Collection<string, string>
     */
    private Collection $joins;
    /**
     * @var MetadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var PathInterface
     */
    private PathInterface $path;

    public function __construct(MetadataRepository $metadataRepository, PathInterface $path)
    {
        if (!class_exists('Doctrine\ORM\Query\Expr')) {
            throw new RuntimeException(
                'For using ' . __CLASS__ . ' you need install [doctrine/orm] <i>composer require doctrine/orm</i>.'
            );
        }
        $this->exp                = new Expr();
        $this->joins              = new Collection();
        $this->metadataRepository = $metadataRepository;
        $this->path               = $path;
    }

    /**
     * @inheritDoc
     */
    public function and(mixed $left, mixed $right): Expr\Andx
    {
        return $this->exp->andX($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function or(mixed $left, mixed $right): Expr\Orx
    {
        return $this->exp->orX($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function eq(mixed $left, mixed $right): Expr\Comparison
    {
        return $this->exp->eq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ne(mixed $left, mixed $right): Expr\Comparison
    {
        return $this->exp->neq($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function gt(mixed $left, mixed $right): Expr\Comparison
    {
        return $this->exp->gt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function ge(mixed $left, mixed $right): Expr\Comparison
    {
        return $this->exp->gte($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function lt(mixed $left, mixed $right): Expr\Comparison
    {
        return $this->exp->lt($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function le(mixed $left, mixed $right): Expr\Comparison
    {
        return $this->exp->lte($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function in(mixed $column, mixed $args): Expr\Func
    {
        return $this->exp->in($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function add(mixed $left, mixed $right): Expr\Math
    {
        return $this->exp->sum($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function sub(mixed $left, mixed $right): Expr\Math
    {
        return $this->exp->diff($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mul(mixed $left, mixed $right): Expr\Math
    {
        return $this->exp->prod($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function div(mixed $left, mixed $right): Expr\Math
    {
        return $this->exp->quot($left, $right);
    }

    /**
     * @inheritDoc
     */
    public function mod(mixed $left, mixed $right): Expr\Math
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(KeyWord::ARITHMETIC_MODULO));
    }

    /**
     * @inheritDoc
     */
    public function not(mixed $args): Expr\Func
    {
        return $this->exp->not($args);
    }

    /**
     * @inheritDoc
     */
    public function toupper(mixed $args): Expr\Func
    {
        return $this->exp->upper($args);
    }

    /**
     * @inheritDoc
     */
    public function tolower(mixed $args): Expr\Func
    {
        return $this->exp->lower($args);
    }

    /**
     * @inheritDoc
     */
    public function trim(mixed $args): Expr\Func
    {
        return $this->exp->trim($args);
    }

    /**
     * @inheritDoc
     */
    public function length(mixed $args): Expr\Func
    {
        return $this->exp->length($args);
    }

    /**
     * @inheritDoc
     */
    public function concat(mixed $column, mixed $args): Expr\Func
    {
        return $this->exp->concat($column, $args);
    }

    /**
     * @inheritDoc
     */
    public function contains(mixed $column, mixed $args): Expr\Comparison
    {
        $args = trim((string)$args, '\'');
        return $this->exp->like($column, "'%{$args}%'");
    }

    /**
     * @inheritDoc
     */
    public function startsWith(mixed $column, mixed $args): Expr\Comparison
    {
        $args = trim((string)$args, '\'');
        return $this->exp->like($column, "'{$args}%'");
    }

    /**
     * @inheritDoc
     */
    public function endsWith(mixed $column, mixed $args): Expr\Comparison
    {
        $args = trim((string)$args, '\'');
        return $this->exp->like($column, "'%{$args}'");
    }

    /**
     * @inheritDoc
     */
    public function substring(mixed $column, mixed $start, $end = null): Expr\Func
    {
        return $this->exp->substring($column, $start, $end);
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
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_MATCHES_PATTERN)
        );
    }

    /**
     * @inheritDoc
     */
    public function ceil(mixed $args): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_CEILING)
        );
    }

    /**
     * @inheritDoc
     */
    public function floor(mixed $args): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_FLOOR)
        );
    }

    /**
     * @inheritDoc
     */
    public function round(mixed $args): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    /**
     * @inheritDoc
     */
    public function literal(mixed $value): Expr\Literal
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format(DATE_ATOM);
        }
        return $this->exp->literal($value);
    }

    public function date(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function day(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function hour(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function minute(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function month(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function second(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function time(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function year(mixed $column): mixed
    {
        throw new ExpressionException(
            Messages::operandOrFunctionNotImplemented(KeyWord::FUNCTION_ROUND)
        );
    }

    public function field(mixed $identifier): mixed
    {
        return $identifier;
    }

    public function be(mixed $column, mixed $args): mixed
    {
        return $this->exp->between($column, ...$args);
    }
}
