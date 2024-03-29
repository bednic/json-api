<?php

declare(strict_types=1);

namespace JSONAPI\URI;

use Stringable;

/**
 * Interface QueryPartInterface
 *
 * @package JSONAPI\URI
 */
interface QueryPartInterface extends Stringable
{
    public const FIELDS_PART_KEY     = 'fields';
    public const FILTER_PART_KEY     = 'filter';
    public const SORT_PART_KEY       = 'sort';
    public const PAGINATION_PART_KEY = 'page';
    public const INCLUSION_PART_KEY  = 'include';

    /**
     * @return string
     */
    public function __toString(): string;
}
