<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering;

/**
 * Class Constants
 *
 * @package JSONAPI\Uri\Filtering
 * @codeCoverageIgnore
 */
class Constants
{
    public const KEYWORD_EQUAL = 'eq';
    public const KEYWORD_NOT_EQUAL = 'neq';
    public const KEYWORD_LOWER_THAN = 'lt';
    public const KEYWORD_LOWER_THAN_OR_EQUAL = 'lte';
    public const KEYWORD_GREATER_THAN = 'gt';
    public const KEYWORD_GREATER_THAN_OR_EQUAL = 'gte';

    public const KEYWORD_NULL = 'null';
    public const KEYWORD_TRUE = 'true';
    public const KEYWORD_FALSE = 'false';
    public const KEYWORD_AND = 'and';
    public const KEYWORD_OR = 'or';

    public const FN_STARTS_WITH = 'startsWith';
    public const FN_ENDS_WITH = 'endsWith';
    public const FN_CONTAINS = 'contains';
    public const FN_IN = 'in';
    public const FN_NOT_IN = 'notIn';

    public const XML_INFINITY_LITERAL = 'INF';
    public const XML_NAN_LITERAL = 'NaN';
}
