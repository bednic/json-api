<?php
// phpcs:ignoreFile
declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

/**
 * Enum Constants
 *
 * @package JSONAPI\URI\Filtering
 * @link    http://docs.oasis-open.org/odata/odata/v4.01/odata-v4.01-part2-url-conventions.html#_Toc31360956
 */
enum KeyWord: string
{
    case PHRASE_SEPARATOR = '|';
    case VALUE_SEPARATOR = '::';

    // LOGICAL
    case LOGICAL_EQUAL = 'eq';
    case LOGICAL_NOT_EQUAL = 'ne';
    case LOGICAL_GREATER_THAN = 'gt';
    case LOGICAL_GREATER_THAN_OR_EQUAL = 'ge';
    case LOGICAL_LOWER_THAN = 'lt';
    case LOGICAL_LOWER_THAN_OR_EQUAL = 'le';
    case LOGICAL_AND = 'and';
    case LOGICAL_OR = 'or';
    case LOGICAL_NOT = 'not';
    case LOGICAL_IN = 'in';
    case LOGICAL_BETWEEN = 'be';

    // ARITHMETIC
    case ARITHMETIC_ADDITION = 'add';
    case ARITHMETIC_SUBTRACTION = 'sub';
    case ARITHMETIC_NEGATION = '-';
    case ARITHMETIC_MULTIPLICATION = 'mul';
    case ARITHMETIC_DIVISION = 'div';
    case ARITHMETIC_MODULO = 'mod';

    // KEYWORD
    case RESERVED_NULL = 'null';
    case RESERVED_TRUE = 'true';
    case RESERVED_FALSE = 'false';
    case RESERVED_INFINITY = 'INF';
    case RESERVED_NOT_A_NUMBER = 'NaN';

    // FUNCTION
    /* String and Collection */
    case FUNCTION_STARTS_WITH = 'startsWith';
    case FUNCTION_ENDS_WITH = 'endsWith';
    case FUNCTION_CONTAINS = 'contains';
    case FUNCTION_CONCAT = 'concat';
    case FUNCTION_INDEX_OF = 'indexof';
    case FUNCTION_LENGTH = 'length';
    case FUNCTION_SUBSTRING = 'substring';

    /* String */
    case FUNCTION_MATCHES_PATTERN = 'matchesPattern';
    case FUNCTION_TO_LOWER = 'tolower';
    case FUNCTION_TO_UPPER = 'toupper';
    case FUNCTION_TRIM = 'trim';

    /* Lambda operators */
    case FUNCTION_ANY = 'any';
    case FUNCTION_ALL = 'all';

    /* Date & time */
    case FUNCTION_DATE = 'date';
    case FUNCTION_DAY = 'day';
    case FUNCTION_HOUR = 'hour';
    case FUNCTION_MINUTE = 'minute';
    case FUNCTION_MONTH = 'month';
    case FUNCTION_NOW = 'now';
    case FUNCTION_SECOND = 'second';
    case FUNCTION_TIME = 'time';
    case FUNCTION_YEAR = 'year';

    /* Arithmetic */
    case FUNCTION_CEILING = 'ceiling';
    case FUNCTION_FLOOR = 'floor';
    case FUNCTION_ROUND = 'round';
}
