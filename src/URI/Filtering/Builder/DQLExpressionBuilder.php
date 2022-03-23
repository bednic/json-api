<?php

/**
 * Created by uzivatel
 * at 02.03.2022 14:30
 */

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Builder;

use ExpressionBuilder\Ex;
use ExpressionBuilder\Expression\Literal;
use JSONAPI\Exception\Metadata\MetadataException;
use JSONAPI\Metadata\MetadataRepository;
use JSONAPI\URI\Filtering\ExpressionBuilder;
use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\Messages;
use JSONAPI\URI\Filtering\UseDottedIdentifier;
use JSONAPI\URI\Path\PathParser;

use function Symfony\Component\String\u;

class DQLExpressionBuilder implements ExpressionBuilder, UseDottedIdentifier
{
    private array $requiredJoins = [];
    /**
     * @var MetadataRepository metadataRepository
     */
    private MetadataRepository $metadataRepository;
    /**
     * @var PathParser pathParser
     */
    private PathParser $pathParser;

    public function __construct(MetadataRepository $metadataRepository, PathParser $pathParser)
    {
        $this->metadataRepository = $metadataRepository;
        $this->pathParser         = $pathParser;
    }

    public function and(mixed $left, mixed $right): mixed
    {
        return Ex::and($left, $right);
    }

    public function or(mixed $left, mixed $right): mixed
    {
        return Ex::or($left, $right);
    }

    public function eq(mixed $left, mixed $right): mixed
    {
        return Ex::eq($left, $right);
    }

    public function ne(mixed $left, mixed $right): mixed
    {
        return Ex::ne($left, $right);
    }

    public function gt(mixed $left, mixed $right): mixed
    {
        return Ex::gt($left, $right);
    }

    public function ge(mixed $left, mixed $right): mixed
    {
        return Ex::ge($left, $right);
    }

    public function lt(mixed $left, mixed $right): mixed
    {
        return Ex::lt($left, $right);
    }

    public function le(mixed $left, mixed $right): mixed
    {
        return Ex::le($left, $right);
    }

    public function in(mixed $column, mixed $args): mixed
    {
        return Ex::in($column, new Literal($args));
    }

    public function has(mixed $column, mixed $args): mixed
    {
        return Ex::has($column, $args);
    }

    public function add(mixed $left, mixed $right): mixed
    {
        return Ex::add($left, $right);
    }

    public function sub(mixed $left, mixed $right): mixed
    {
        return Ex::sub($left, $right);
    }

    public function mul(mixed $left, mixed $right): mixed
    {
        return Ex::mul($left, $right);
    }

    public function div(mixed $left, mixed $right): mixed
    {
        return Ex::div($left, $right);
    }

    public function mod(mixed $left, mixed $right): mixed
    {
        return Ex::mod($left, $right);
    }

    public function not(mixed $args): mixed
    {
        return Ex::not($args);
    }

    public function toupper(mixed $args): mixed
    {
        return Ex::toUpper($args);
    }

    public function tolower(mixed $args): mixed
    {
        return Ex::toLower($args);
    }

    public function trim(mixed $args): mixed
    {
        return Ex::trim($args);
    }

    public function length(mixed $args): mixed
    {
        return Ex::length($args);
    }

    public function concat(mixed $column, mixed $args): mixed
    {
        return Ex::concat($column, $args);
    }

    public function contains(mixed $column, mixed $args): mixed
    {
        return Ex::contains($column, $args);
    }

    public function startsWith(mixed $column, mixed $args): mixed
    {
        return Ex::startsWith($column, $args);
    }

    public function endsWith(mixed $column, mixed $args): mixed
    {
        return Ex::endsWith($column, $args);
    }

    public function substring(mixed $column, mixed $start, mixed $end = null): mixed
    {
        return Ex::substring($column, $start, $end);
    }

    public function indexOf(mixed $column, mixed $args): mixed
    {
        return Ex::indexOf($column, $args);
    }

    public function pattern(mixed $column, mixed $args): mixed
    {
        return Ex::matchesPattern($column, $args);
    }

    public function ceil(mixed $args): mixed
    {
        return Ex::ceiling($args);
    }

    public function floor(mixed $args): mixed
    {
        return Ex::floor($args);
    }

    public function round(mixed $args): mixed
    {
        return Ex::round($args);
    }

    public function isNull(mixed $column): mixed
    {
        return Ex::eq($column, new Literal(null));
    }

    public function isNotNull(mixed $column): mixed
    {
        return Ex::ne($column, new Literal(null));
    }

    public function literal(mixed $value): mixed
    {
        return Ex::literal($value);
    }

    public function date(mixed $column): mixed
    {
        return Ex::date($column);
    }

    public function day(mixed $column): mixed
    {
        return Ex::day($column);
    }

    public function hour(mixed $column): mixed
    {
        return Ex::hour($column);
    }

    public function minute(mixed $column): mixed
    {
        return Ex::minute($column);
    }

    public function month(mixed $column): mixed
    {
        return Ex::month($column);
    }

    public function now(): mixed
    {
        throw new ExpressionException(Messages::operandOrFunctionNotImplemented(Constants::FUNCTION_NOW));
    }

    public function second(mixed $column): mixed
    {
        return Ex::second($column);
    }

    public function time(mixed $column): mixed
    {
        return Ex::time($column);
    }

    public function year(mixed $column): mixed
    {
        return Ex::year($column);
    }

    /**
     * @param string $identifier
     *
     * @return mixed
     * @throws MetadataException
     */
    public function parseIdentifier(string $identifier): mixed
    {
        /*
         * |=========================|
         * | CASES                   |
         * |=========================|
         * | field                   |
         * |-------------------------|
         * | embedded.field          |
         * |-------------------------|
         * | relation.field          |
         * |-------------------------|
         * | relation.embedded.field |
         * |-------------------------|
         */
        $metadata = $this->metadataRepository->getByType($this->pathParser->getPrimaryResourceType());
        $field    = [];
        $prefix   = u($metadata->getType())->snake()->toString();
        $parts    = explode('.', $identifier);
        while ($part = array_shift($parts)) {
            if ($metadata->hasRelationship($part)) {
                $relationship                             = $metadata->getRelationship($part);
                $this->requiredJoins[$relationship->name] = $prefix . '.' . $part;
                $prefix                                   = $relationship->name;
                $metadata                                 = $this->metadataRepository->getByClass(
                    $relationship->target
                );
            } elseif ($metadata->hasAttribute($part)) {
                $field[] = $part;
            } else {
                $field[] = $part;
            }
        }
        return Ex::field($prefix . '.' . join('.', $field));
    }

    public function getRequiredJoins(): array
    {
        return $this->requiredJoins;
    }
}
