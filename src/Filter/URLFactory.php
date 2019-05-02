<?php


namespace JSONAPI\Filter;

/**
 * The trick in this should be, that PHP store static values in cache,
 * so I expect that instance of URL class will be created only once.
 *
 * Class URLFactory
 *
 * @package JSONAPI\Filter
 */
class URLFactory
{
    /**
     * @var URL
     */
    private static $query;

    public static function create(): URL
    {
        if (!self::$query) {
            self::$query = new URL();
        }
        return self::$query;
    }
}
