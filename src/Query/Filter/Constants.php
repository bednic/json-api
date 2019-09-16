<?php


namespace JSONAPI\Query\Filter;


class Constants
{
    const KEYWORD_EQUAL = 'eq';
    const KEYWORD_NOT_EQUAL = 'neq';
    const KEYWORD_LOWER_THAN = 'lt';
    const KEYWORD_LOWER_THAN_OR_EQUAL = 'lte';
    const KEYWORD_GREATER_THAN = 'gt';
    const KEYWORD_GREATER_THAN_OR_EQUAL = 'gte';

    const KEYWORD_NULL = 'null';
    const KEYWORD_TRUE = 'true';
    const KEYWORD_FALSE = 'false';
    const KEYWORD_AND = 'and';
    const KEYWORD_OR = 'or';

    const FN_STARTS_WITH = 'startsWith';
    const FN_ENDS_WITH = 'endsWith';
    const FN_CONTAINS = 'contains';
    const FN_IN = 'in';
    const FN_NOT_IN = 'notIn';

    const XML_INFINITY_LITERAL = 'INF';
    const XML_NAN_LITERAL = 'NaN';
}
