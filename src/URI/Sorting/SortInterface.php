<?php

declare(strict_types=1);

namespace JSONAPI\URI\Sorting;

use JSONAPI\URI\QueryPartInterface;

/**
 * Interface SortInterface
 *
 * @package JSONAPI\URI\Sorting
 */
interface SortInterface extends QueryPartInterface
{
    public const ASC  = 'ASC';
    public const DESC = 'DESC';

    /**
     * @return array associative array contains field as key and order as value
     * @example [
     *      "fieldA" => "DESC",
     *      "fieldB" => "ASC"
     * ]
     */
    public function getOrder(): array;
}
