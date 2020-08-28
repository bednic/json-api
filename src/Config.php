<?php

declare(strict_types=1);

namespace JSONAPI;

/**
 * Class Config
 *
 * @package JSONAPI
 */
class Config
{
    /**
     * @var string
     */
    public static string $ENDPOINT = '';

    /**
     * Should by positive integer. Disable limit by passing -1.
     *
     * @var int
     */
    public static int $MAX_INCLUDED_ITEMS = 625;

    /**
     * @var int
     */
    public static int $RELATIONSHIP_LIMIT = 25;

    /**
     * @var bool
     */
    public static bool $RELATIONSHIP_DATA = true;

    /**
     * Enables inclusion support
     *
     * @var bool
     */
    public static bool $INCLUSION_SUPPORT = true;
    /**
     * Enables sort support
     *
     * @var bool
     */
    public static bool $SORT_SUPPORT = true;

    /**
     * Enables pagination support
     *
     * @var bool
     */
    public static bool $PAGINATION_SUPPORT = true;
}
