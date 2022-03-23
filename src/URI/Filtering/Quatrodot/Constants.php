<?php

/**
 * Created by uzivatel
 * at 22.03.2022 15:03
 */

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\Quatrodot;

/**
 * Enum Constants
 *
 * @package JSONAPI\URI\Filtering\Quatrodot
 */
enum Constants: string
{
    case PHRASE_SEPARATOR = '|';
    case VALUE_SEPARATOR = '::';

    case LOGICAL_EQUAL = 'eq';
    case LOGICAL_NOT_EQUAL = 'ne';
    case LOGICAL_GREATER_THAN = 'gt';
    case LOGICAL_GREATER_THAN_OR_EQUAL = 'ge';
    case LOGICAL_LOWER_THAN = 'lt';
    case LOGICAL_LOWER_THAN_OR_EQUAL = 'le';

    case FUNCTION_STARTS_WITH = 'startsWith';
    case FUNCTION_ENDS_WITH = 'endsWith';
    case FUNCTION_CONTAINS = 'contains';

}
