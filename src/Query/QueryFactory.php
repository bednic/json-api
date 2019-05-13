<?php


namespace JSONAPI\Query;

use JSONAPI\Exception\QueryException;

/**
 * The trick in this should be, that PHP store static values in cache,
 * so I expect that instance of Query class will be created only once.
 *
 * Class QueryFactory
 *
 * @package JSONAPI\Query
 */
class QueryFactory
{
    /**
     * @var Query
     */
    private static $query;

    /**
     * @return Query
     * @throws QueryException
     */
    public static function create(): Query
    {
        if (!self::$query) {
            self::$query = new Query();
        }
        return self::$query;
    }
}
