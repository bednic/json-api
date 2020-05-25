<?php

declare(strict_types=1);

namespace JSONAPI\Uri\Filtering;

/**
 * Class Constants
 *
 * @package JSONAPI\Uri\Filtering
 * @codeCoverageIgnore
 * @link http://docs.oasis-open.org/odata/odata/v4.01/odata-v4.01-part2-url-conventions.html#_Toc31360956
 */
class Constants
{
    // LOGICAL
    public const LOGICAL_EQUAL = 'eq';
    public const LOGICAL_NOT_EQUAL = 'ne';
    public const LOGICAL_GREATER_THAN = 'gt';
    public const LOGICAL_GREATER_THAN_OR_EQUAL = 'ge';
    public const LOGICAL_LOWER_THAN = 'lt';
    public const LOGICAL_LOWER_THAN_OR_EQUAL = 'le';
    public const LOGICAL_AND = 'and';
    public const LOGICAL_OR = 'or';
    public const LOGICAL_NOT = 'not';
    public const LOGICAL_HAS = 'has';
    public const LOGICAL_IN = 'in';

    // ARITHMETIC
    public const ARITHMETIC_ADDITION = 'add';
    public const ARITHMETIC_SUBTRACTION = 'sub';
    public const ARITHMETIC_NEGATION = '-';
    public const ARITHMETIC_MULTIPLICATION = 'mul';
    public const ARITHMETIC_DIVISION = 'div';
    public const ARITHMETIC_MODULO = 'mod';

    // KEYWORD
    public const KEYWORD_NULL = 'null';
    public const KEYWORD_TRUE = 'true';
    public const KEYWORD_FALSE = 'false';
    public const KEYWORD_INFINITY = 'INF';
    public const KEYWORD_NOT_A_NUMBER = 'NaN';

    // FUNCTION
    /* String and Collection */
    public const FUNCTION_STARTS_WITH = 'startsWith';
    public const FUNCTION_ENDS_WITH = 'endsWith';
    public const FUNCTION_CONTAINS = 'contains';
    public const FUNCTION_CONCAT = 'concat';
    public const FUNCTION_INDEX_OF = 'indexof';
    public const FUNCTION_LENGTH = 'length';
    public const FUNCTION_SUBSTRING = 'substring';

    /* String */
    public const FUNCTION_MATCHES_PATTERN = 'matchesPattern';
    public const FUNCTION_TO_LOWER = 'tolower';
    public const FUNCTION_TO_UPPER = 'toupper';
    public const FUNCTION_TRIM = 'trim';

    /* Lambda operators */
    public const FUNCTION_ANY = 'any';
    public const FUNCTION_ALL = 'all';

    /* Date & time */
    public const FUNCTION_DATE = 'date';
    public const FUNCTION_DAY = 'day';
    public const FUNCTION_HOUR = 'hour';
    public const FUNCTION_MINUTE = 'minute';
    public const FUNCTION_MONTH = 'month';
    public const FUNCTION_NOW = 'now';
    public const FUNCTION_SECOND = 'second';
    public const FUNCTION_TIME = 'time';
    public const FUNCTION_YEAR = 'year';

    /* Arithmetic */
    public const FUNCTION_CEILING = 'ceiling';
    public const FUNCTION_FLOOR = 'floor';
    public const FUNCTION_ROUND = 'round';
}
